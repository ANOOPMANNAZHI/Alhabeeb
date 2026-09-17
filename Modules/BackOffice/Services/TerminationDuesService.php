<?php

namespace Modules\BackOffice\Services;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Modules\BackOffice\Entities\Termination;
use Modules\BackOffice\Entities\TerminationChecklist;
use Modules\BackOffice\Entities\TerminationDues;
use Modules\BackOffice\Entities\TerminationDuesAllocation;
use Modules\Sales\Entities\TenantContract;
use Modules\BackOffice\Services\TerminationDuesCategory as Cat;

/**
 * Creates a dues record when a termination completes, recomputes its
 * balances from live sources, and answers the two questions other screens
 * ask: "what does this contract still owe?" and "may this rent receipt be
 * saved?".
 */
class TerminationDuesService
{
    const FINAL_STAGE = 505;
    const INSPECTION_STAGE = 503;

    /**
     * Called from the 505 block of TenantTerminationController. Idempotent:
     * a contract that already has dues is left alone.
     *
     * @return TerminationDues|null null when nothing is owed
     */
    public function createForTermination(Termination $termination, $userId = null)
    {
        $existing = TerminationDues::where('tenant_contract_id', $termination->contract_id)->first();
        if ($existing) {
            return $existing;
        }

        $input = $this->buildInputFor($termination->contract_id, $termination);
        $lines = TerminationDuesBuilder::build($input);
        if (empty($lines)) {
            return null;
        }

        $inspection = Termination::where('contract_id', $termination->contract_id)
            ->where('work_flow_processes_code', self::INSPECTION_STAGE)
            ->orderBy('id', 'desc')->first();

        $uptoId = DB::table('receipts_generation')
            ->where('tenant_contract_id', $termination->contract_id)
            ->where('receipts_generation_type', 0)
            // 3 = approved, 6 = approved and posted to AX; both are real money
            ->whereIn('receipts_generation_approval_status', [3, 6])
            ->where('receipts_generation_status', '<>', 2)
            ->whereNull('deleted_at')
            ->max('id');

        return DB::transaction(function () use ($termination, $lines, $inspection, $uptoId, $userId) {
            $dues = TerminationDues::create([
                'tenant_contract_id'   => $termination->contract_id,
                'termination_id'       => $termination->id,
                'termination_date'     => $termination->termination_date,
                'terminated_at'        => $termination->created_at ?: now(),
                'charges_fixed_at'     => $inspection && $inspection->created_at ? $inspection->created_at : ($termination->created_at ?: now()),
                'rent_receipts_upto_id'=> $uptoId,
                'status'               => TerminationDues::STATUS_OPEN,
                'created_by'           => $userId,
            ]);
            foreach ($lines as $l) {
                $dues->lines()->create($l);
            }
            $this->refresh($dues);
            return $dues;
        });
    }

    /**
     * Builder input from the inspection checklist and the termination row.
     * Public so the backfill command can reuse it.
     */
    public function buildInputFor($contractId, Termination $finalRow)
    {
        // Checklist rows of the latest inspection for this contract
        $latestTerminationId = TerminationChecklist::where('termination_contract_id', $contractId)->max('termination_id');
        $rows = $latestTerminationId
            ? TerminationChecklist::with(['work', 'subWorks'])->where('termination_contract_id', $contractId)->where('termination_id', $latestTerminationId)->orderBy('id')->get()
            : collect();

        $other = [];
        $works = [];
        foreach ($rows as $row) {
            if ($row->work_id) {
                $desc = trim((string) (optional($row->work)->works_desc));
                $sub  = trim((string) (optional($row->subWorks)->sub_work));
                $works[] = [
                    'id'          => $row->id,
                    'description' => trim($desc . ($sub !== '' ? ' - ' . $sub : '')) ?: 'Maintenance item',
                    'amount'      => $row->termination_amount,
                ];
            } elseif ($row->termination_other_work) {
                $other[] = ['id' => $row->id, 'name' => $row->termination_other_work, 'amount' => $row->termination_amount];
            }
        }

        // Rent outstanding as on the termination date, same formula as the termination flow
        $rentOs = 0;
        $terminationDate = $finalRow->termination_date ? date('Y-m-d', strtotime($finalRow->termination_date)) : date('Y-m-d');
        try {
            $sumOfReceipt = DB::table('receipts_generation')
                ->where('receipts_generation_type', 0)
                ->where('tenant_contract_id', $contractId)
                ->whereIn('receipts_generation_approval_status', [3, 6])
                ->where('receipts_generation_status', '<>', 2)
                ->whereNull('deleted_at')
                ->sum('receipts_generation_amt');
            $rentOs = (float) totalContractRentCountCalculation($contractId, $terminationDate) - (float) $sumOfReceipt;
        } catch (\Exception $e) {
            Log::warning('TerminationDues: rent OS calculation failed for contract ' . $contractId . ': ' . $e->getMessage());
        }

        // Amounts live on the row that carries the inspection figures
        $figures = Termination::where('contract_id', $contractId)->whereNotNull('termination_total_amount')->orderBy('id', 'desc')->first() ?: $finalRow;
        $ew = $figures->termination_total_elec_water_amount;
        if ($ew === null || $ew === '') {
            $ew = (float) $figures->termination_electricity_amount + (float) $figures->termination_water_amount;
        }

        return [
            'rent_os'              => $rentOs,
            'checklist_other'      => $other,
            'checklist_works'      => $works,
            'elec_water'           => $ew,
            'maintenance_discount' => $figures->termination_discount_maintenance_due,
        ];
    }

    /**
     * Recompute from live sources and persist the summary columns.
     * @return array the TerminationDuesSettlement::compute() result plus 'sources', 'pending', 'account_map'
     */
    public function refresh(TerminationDues $dues)
    {
        $dues->loadMissing('lines');
        $sources = new TerminationDuesSources();
        $lines = $dues->lines->map(function ($l) {
            return ['id' => $l->id, 'category' => $l->category, 'owner_team' => $l->owner_team, 'description' => $l->description, 'amount' => $l->amount];
        })->all();
        $manual = TerminationDuesAllocation::whereIn('termination_dues_line_id', array_column($lines, 'id'))->orderBy('id')->get()->toArray();
        $src = $sources->forDues($dues);

        $result = TerminationDuesSettlement::compute($lines, $src, $manual);
        $result['sources'] = $src;
        $result['pending'] = $sources->pendingForDues($dues);
        $result['account_map'] = $sources->accountMap();

        $latestFollowup = $dues->followups()->first();
        $dues->forceFill([
            'status'              => $result['status'],
            'total_owed'          => $result['total']['owed'],
            'total_settled'       => $result['total']['settled'] + $result['total']['waived'],
            'balance'             => $result['total']['balance'],
            'backoffice_balance'  => $result['teams'][Cat::TEAM_BACKOFFICE]['balance'],
            'maintenance_balance' => $result['teams'][Cat::TEAM_MAINTENANCE]['balance'],
            'last_followup_at'    => $latestFollowup ? $latestFollowup->created_at : null,
            'next_promise_date'   => $latestFollowup ? $latestFollowup->promise_date : null,
        ])->save();

        return $result;
    }

    /** Safe to call from any hook: never throws. */
    public static function touchContract($contractId)
    {
        if (!$contractId) {
            return;
        }
        try {
            $dues = TerminationDues::where('tenant_contract_id', $contractId)->first();
            if ($dues) {
                (new self)->refresh($dues);
            }
        } catch (\Exception $e) {
            Log::warning('TerminationDues refresh failed for contract ' . $contractId . ': ' . $e->getMessage());
        }
    }

    public static function touchReceipt($receiptId)
    {
        try {
            $contractId = DB::table('receipts_generation')->where('id', $receiptId)->value('tenant_contract_id');
        } catch (\Exception $e) {
            Log::warning('TerminationDues touchReceipt lookup failed for receipt ' . $receiptId . ': ' . $e->getMessage());
            return;
        }
        self::touchContract($contractId);
    }

    public static function touchDepositRefund($depositRefundId)
    {
        try {
            $contractId = DB::table('deposit_refund')->where('id', $depositRefundId)->value('tenant_contract_id');
        } catch (\Exception $e) {
            Log::warning('TerminationDues touchDepositRefund lookup failed for deposit refund ' . $depositRefundId . ': ' . $e->getMessage());
            return;
        }
        self::touchContract($contractId);
    }

    /**
     * Compact figures for the summary box on the receipt / deposit refund forms.
     * @return array|null null when the contract has no dues
     */
    public function summaryForContract($contractId)
    {
        $dues = TerminationDues::where('tenant_contract_id', $contractId)->first();
        if (!$dues) {
            return null;
        }
        $r = $this->refresh($dues);
        $categories = [];
        foreach ($r['lines'] as $l) {
            $c = $l['category'];
            if (!isset($categories[$c])) {
                $categories[$c] = ['label' => Cat::label($c), 'owner_team' => $l['owner_team'], 'owed' => 0.0, 'settled' => 0.0, 'balance' => 0.0];
            }
            $categories[$c]['owed'] += $l['owed'];
            $categories[$c]['settled'] += $l['settled'] + $l['waived'];
            $categories[$c]['balance'] += $l['balance'];
        }
        return [
            'dues_id'          => $dues->id,
            'url'              => route('termination-dues.show', $dues->id),
            'status'           => $r['status'],
            'termination_date' => $dues->termination_date ? $dues->termination_date->format('d/m/Y') : null,
            'balance'          => $r['total']['balance'],
            'teams'            => $r['teams'],
            'categories'       => array_values($categories),
        ];
    }

    /**
     * Guard for rent receipts on terminated contracts.
     * @return string|null error message, or null when the receipt may be saved
     */
    public function rentReceiptError($contractId, $amount, $effTo = null, $ignoreReceiptId = null)
    {
        $contract = TenantContract::where('id', $contractId)->first(['id', 'tenant_renewal_termination_status', 'tenant_contract_no']);
        if (!$contract || (int) $contract->tenant_renewal_termination_status !== 8) {
            return null;
        }
        $dues = TerminationDues::where('tenant_contract_id', $contractId)->first();
        if (!$dues) {
            return 'Contract ' . $contract->tenant_contract_no . ' is terminated and has no termination dues on record. Rent receipts cannot be created on it; if rent is genuinely outstanding ask an administrator to run the termination dues backfill.';
        }
        $r = $this->refresh($dues);
        $rentBalance = 0.0;
        foreach ($r['lines'] as $l) {
            if ($l['category'] === Cat::RENT) {
                $rentBalance += $l['balance'];
            }
        }
        if ($ignoreReceiptId) {
            // Editing an approved receipt that is already counted: give its amount back to the balance
            $current = DB::table('receipts_generation')->where('id', $ignoreReceiptId)->whereIn('receipts_generation_approval_status', [3, 6])->where('receipts_generation_status', '<>', 2)->value('receipts_generation_amt');
            $rentBalance += (float) $current;
        }
        $amount = (float) $amount;
        if ($rentBalance <= TerminationDuesSettlement::EPS) {
            return 'Contract ' . $contract->tenant_contract_no . ' is terminated and its rent dues are fully settled (see Termination Dues). No further rent receipt can be created.';
        }
        if ($amount > $rentBalance + TerminationDuesSettlement::EPS) {
            return 'Receipt amount ' . numberFormat($amount) . ' exceeds the open rent dues of ' . numberFormat($rentBalance) . ' on terminated contract ' . $contract->tenant_contract_no . '. Enter at most ' . numberFormat($rentBalance) . '.';
        }
        if ($effTo && $dues->termination_date && strtotime($effTo) > $dues->termination_date->getTimestamp()) {
            return 'Effective-to date ' . date('d/m/Y', strtotime($effTo)) . ' is after the termination date ' . $dues->termination_date->format('d/m/Y') . '. Rent cannot be collected for a period after termination.';
        }
        return null;
    }
}
