<?php

namespace Modules\Masters\Services;

use App\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Notification;
use Modules\BackOffice\Services\LegalMessages;
use Modules\BackOffice\Services\RentOutstanding;
use Modules\Masters\Entities\Legal;
use Modules\Masters\Notifications\LegalNotification;

/**
 * Legal settlements (plan 2026-10-13, phase 3).
 *
 * Out of court or court decided: the lawyer enters the amount, date and installments; PLM verifies
 * (waiver, fees recovered, final total = sum of installments) or sends it back with a reason.
 * Collected = receipts (rent and general) on the case's contract dated on or after the agreed date,
 * approved / posted and not cancelled - the same counting rule as RentOutstanding. Collected money is
 * applied to the installments oldest first; post-dated cheques not yet receipted show as "cheque held"
 * but do not count as paid. Fully collected -> Completed; an installment overdue more than BROKEN_DAYS
 * -> Broken (the lawyer may then move the case to any stage again and enter a new settlement).
 */
class LegalSettlement
{
    const PENDING = 0;
    const ACTIVE = 1;
    const COMPLETED = 2;
    const BROKEN = 3;
    const SENT_BACK = 4;
    const CANCELLED = 5;

    const STATUS = [0 => 'Pending verification', 1 => 'Active', 2 => 'Completed', 3 => 'Broken', 4 => 'Sent back', 5 => 'Cancelled'];
    const TYPES = [1 => 'Out of court', 2 => 'Court decided'];
    const BROKEN_DAYS = 30;
    const RECEIPT_TYPES = [0, 1];
    const EPS = 0.0005;

    /** The settlement that matters now: newest one that is not cancelled. */
    public static function current($legalId)
    {
        return DB::table('legal_settlements')->where('legal_id', $legalId)->where('status', '<>', self::CANCELLED)
            ->orderBy('id', 'desc')->first();
    }

    public static function all($legalId)
    {
        return DB::table('legal_settlements')->where('legal_id', $legalId)->orderBy('id', 'desc')->get();
    }

    public static function installments($settlementId)
    {
        return DB::table('legal_settlement_installments')->where('settlement_id', $settlementId)
            ->orderBy('due_date')->orderBy('sort_order')->get();
    }

    /** A new settlement can be entered when there is none, or the last one is broken / cancelled. */
    public static function canEnter(Legal $legal)
    {
        if (!LegalCase::isActive($legal)) {
            return false;
        }
        $s = self::current($legal->id);
        return !$s || (int) $s->status === self::BROKEN;
    }

    /** Court decided settlements come from a judgment: the case must be at Judgment or later. */
    public static function judgmentReached(Legal $legal)
    {
        $judgment = DB::table('legal_stages')->where('code', 'judgment')->value('sort_order');
        $current = LegalCase::stage($legal->stage_id);
        return $judgment !== null && $current && (int) $current->sort_order >= (int) $judgment;
    }

    /** Money collected towards a settlement: receipts on the contract from the agreed date. */
    public static function collected($settlement, $contractId)
    {
        return round((float) DB::table('receipts_generation')
            ->where('tenant_contract_id', $contractId)
            ->whereIn('receipts_generation_type', self::RECEIPT_TYPES)
            ->whereIn('receipts_generation_approval_status', RentOutstanding::COUNTED_APPROVAL)
            ->where('receipts_generation_status', '<>', RentOutstanding::STATUS_CANCELLED)
            ->whereNull('deleted_at')
            ->whereDate('receipts_generation_receipt_date', '>=', $settlement->agreed_date)
            ->sum('receipts_generation_amt'), 3);
    }

    /**
     * Rent and general receipts on the contract dated from the agreed date, for display: every one of them, with
     * whether it counts (same rule as collected()) and why not - waiting approval, rejected, cancelled.
     */
    public static function receipts($settlement, $contractId)
    {
        $rows = DB::table('receipts_generation')
            ->where('tenant_contract_id', $contractId)
            ->whereIn('receipts_generation_type', self::RECEIPT_TYPES)
            ->whereNull('deleted_at')
            ->whereDate('receipts_generation_receipt_date', '>=', $settlement->agreed_date)
            ->orderBy('receipts_generation_receipt_date')->orderBy('id')
            ->get(['id', 'receipts_generation_receipt_no as receipt_no', 'receipts_generation_receipt_date as receipt_date',
                   'receipts_generation_amt as amount', 'receipts_generation_type as type', 'receipts_generation_payment_method as method',
                   'receipts_generation_cheque_no as cheque_no', 'receipts_generation_approval_status as approval',
                   'receipts_generation_status as status']);
        foreach ($rows as $r) {
            $cancelled = (int) $r->status === RentOutstanding::STATUS_CANCELLED;
            $r->counts = !$cancelled && in_array((int) $r->approval, RentOutstanding::COUNTED_APPROVAL, true);
            if ($cancelled) {
                $r->state = ['Cancelled', 'danger'];
            } elseif ((int) $r->approval === 6) {
                $r->state = ['Posted', 'success'];
            } elseif ((int) $r->approval === 3) {
                $r->state = ['Approved', 'success'];
            } elseif ((int) $r->approval === 4) {
                $r->state = ['Rejected', 'danger'];
            } else {
                $r->state = ['Waiting approval', 'warning'];
            }
            $r->type_label = (int) $r->type === 1 ? 'General' : 'Rent';
            $r->method_label = (int) $r->method === 1 ? 'Cheque' : ((int) $r->method === 3 ? 'Bank transfer' : 'Cash');
        }
        return $rows;
    }

    /** Cheques received for the contract, dated from the agreed date, not yet receipted, cancelled or bounced. */
    public static function heldCheques($settlement, $contractId)
    {
        return DB::table('pdc')->where('tenant_contract_id', $contractId)
            ->whereDate('pdc_check_date', '>=', $settlement->agreed_date)
            ->whereNull('pdc_receipt_no')->whereNull('pdc_cancel_date')
            ->where(function ($q) { $q->whereNull('pdc_is_saved')->orWhere('pdc_is_saved', '<>', 2); })
            ->orderBy('pdc_check_date')->get(['id', 'pdc_check_no', 'pdc_check_date', 'pdc_amt']);
    }

    /**
     * Installment rows with paid / held / status, and the totals.
     * status: paid, partly paid, cheque held, overdue, due.
     */
    public static function schedule($settlement, $contractId, $today = null)
    {
        $today = $today ?: date('Y-m-d');
        $collected = self::collected($settlement, $contractId);
        $cheques = self::heldCheques($settlement, $contractId);
        $held = round((float) $cheques->sum('pdc_amt'), 3);
        $paidLeft = $collected;
        $heldLeft = $held;
        $rows = [];
        foreach (self::installments($settlement->id) as $i) {
            $amount = (float) $i->amount;
            $paid = min($amount, $paidLeft);
            $paidLeft -= $paid;
            $heldPart = min($amount - $paid, $heldLeft);
            $heldLeft -= $heldPart;
            $open = $amount - $paid;
            if ($open <= self::EPS) {
                $status = 'paid';
            } elseif ($open - $heldPart <= self::EPS) {
                $status = 'cheque held';
            } elseif ($i->due_date < $today) {
                $status = 'overdue';
            } elseif ($paid > self::EPS) {
                $status = 'partly paid';
            } else {
                $status = 'due';
            }
            $rows[] = (object) ['id' => $i->id, 'due_date' => $i->due_date, 'amount' => $amount, 'paid' => round($paid, 3),
                'held' => round($heldPart, 3), 'status' => $status,
                'days_overdue' => $status === 'overdue' ? (int) floor((strtotime($today) - strtotime($i->due_date)) / 86400) : 0,
                'overdue_alerted_at' => $i->overdue_alerted_at];
        }
        $total = (float) $settlement->settled_total;
        return ['rows' => $rows, 'collected' => $collected, 'held' => $held, 'cheques' => $cheques,
            'total' => $total, 'balance' => round(max(0, $total - $collected), 3)];
    }

    /** Validate installment rows [due_date, amount]; returns [rows, error]. */
    public static function cleanInstallments($dates, $amounts, $total)
    {
        $rows = [];
        foreach ((array) $dates as $k => $d) {
            $a = isset($amounts[$k]) ? $amounts[$k] : null;
            if (trim((string) $d) === '' && trim((string) $a) === '') {
                continue;
            }
            if (!strtotime((string) $d) || !is_numeric($a) || (float) $a <= 0) {
                return [[], 'Every installment needs a date and an amount above zero.'];
            }
            $rows[] = ['due_date' => date('Y-m-d', strtotime($d)), 'amount' => round((float) $a, 3)];
        }
        if (!$rows) {
            return [[], 'Add at least one installment (one row for a single payment).'];
        }
        $sum = round(array_sum(array_column($rows, 'amount')), 3);
        if (abs($sum - round((float) $total, 3)) > self::EPS) {
            return [[], 'The installments add up to ' . numberFormat($sum) . ' but the total is ' . numberFormat($total) . '.'];
        }
        usort($rows, function ($x, $y) { return strcmp($x['due_date'], $y['due_date']); });
        return [$rows, null];
    }

    public static function saveInstallments($settlementId, array $rows)
    {
        DB::table('legal_settlement_installments')->where('settlement_id', $settlementId)->delete();
        $now = now();
        foreach ($rows as $k => $r) {
            DB::table('legal_settlement_installments')->insert($r + ['settlement_id' => $settlementId, 'sort_order' => $k + 1,
                'created_at' => $now, 'updated_at' => $now]);
        }
    }

    /**
     * Status from the money: Active <-> Completed, Active -> Broken. Notifies on each change.
     * Only for settlements of open cases; closed cases are watched by shortfallCheck().
     */
    public static function refresh($settlement, $notify = true)
    {
        $status = (int) $settlement->status;
        if (!in_array($status, [self::ACTIVE, self::COMPLETED], true)) {
            return $settlement;
        }
        $legal = Legal::find($settlement->legal_id);
        if (!$legal || $legal->closed_at) {
            return $settlement;
        }
        $s = self::schedule($settlement, $legal->tenant_contract_id);
        $now = now();

        if ($status === self::ACTIVE && $s['collected'] + self::EPS >= $s['total']) {
            DB::table('legal_settlements')->where('id', $settlement->id)->update(['status' => self::COMPLETED, 'completed_at' => $now, 'updated_at' => $now]);
            LegalCase::log($legal->id, 'settlement', 'Settlement fully collected (' . numberFormat($s['collected']) . '). The case can be closed.', 1);
            if ($notify) {
                self::notify($legal, 'legal_settlement_completed', [], 'Settlement fully paid');
            }
        } elseif ($status === self::COMPLETED && $s['collected'] + self::EPS < $s['total']) {
            DB::table('legal_settlements')->where('id', $settlement->id)->update(['status' => self::ACTIVE, 'completed_at' => null, 'updated_at' => $now]);
            LegalCase::log($legal->id, 'settlement', 'A payment was cancelled: ' . numberFormat($s['balance']) . ' of the settlement is unpaid again.', 1);
            if ($notify) {
                self::notify($legal, 'legal_settlement_shortfall', ['short' => numberFormat($s['balance'])], 'Settlement payment cancelled');
            }
            $status = self::ACTIVE;
        }
        if ($status === self::ACTIVE) {
            $worst = 0;
            foreach ($s['rows'] as $r) {
                $worst = max($worst, $r->days_overdue);
            }
            if ($worst > self::BROKEN_DAYS) {
                DB::table('legal_settlements')->where('id', $settlement->id)->update(['status' => self::BROKEN, 'broken_at' => $now, 'updated_at' => $now]);
                LegalCase::log($legal->id, 'settlement_broken', 'Settlement broken: an installment is ' . $worst . ' days overdue. The case is active again.', 1);
                // the deal is off: its waiver no longer applies and the amount is owed again
                if (LegalWaiver::cancel($legal->id, $settlement->id, 'Settlement broken')) {
                    LegalCase::log($legal->id, 'settlement', 'Waiver of ' . numberFormat($settlement->waiver_amount) . ' cancelled with the broken settlement.', 1);
                }
                if ($notify) {
                    self::notify($legal, 'legal_settlement_broken', ['days' => self::BROKEN_DAYS], 'Settlement broken');
                }
            }
        }
        return DB::table('legal_settlements')->where('id', $settlement->id)->first();
    }

    /** A closed, settled case whose money went missing (cheque bounced): alert once. */
    public static function shortfallCheck(Legal $legal)
    {
        if (!$legal->closed_at || !in_array($legal->close_reason, ['paid_in_full', 'written_off'], true)) {
            return;
        }
        $settlement = self::current($legal->id);
        if (!$settlement || $settlement->shortfall_alerted_at || (int) $settlement->status !== self::COMPLETED) {
            return;
        }
        $s = self::schedule($settlement, $legal->tenant_contract_id);
        if ($s['collected'] + self::EPS < $s['total']) {
            DB::table('legal_settlements')->where('id', $settlement->id)->update(['shortfall_alerted_at' => now()]);
            LegalCase::log($legal->id, 'settlement', 'After closing, a payment was cancelled: ' . numberFormat($s['balance']) . ' unpaid. Reopen the case if needed.', 1);
            self::notify($legal, 'legal_settlement_shortfall', ['short' => numberFormat($s['balance'])], 'Settled case lost a payment - reopen?');
        }
    }

    /** Called whenever receipts of a contract change (TerminationDuesService::touchContract). */
    public static function touchContract($contractId)
    {
        try {
            foreach (Legal::where('tenant_contract_id', $contractId)->get() as $legal) {
                if ($legal->closed_at) {
                    self::shortfallCheck($legal);
                    continue;
                }
                $settlement = self::current($legal->id);
                if ($settlement) {
                    self::refresh($settlement);
                }
            }
        } catch (\Exception $e) {
            Log::warning('Legal settlement refresh failed for contract ' . $contractId . ': ' . $e->getMessage());
        }
    }

    /** Daily run: statuses, overdue alerts (once per installment), shortfalls on closed settled cases. */
    public static function checkAll()
    {
        $counts = ['refreshed' => 0, 'overdue_alerts' => 0, 'shortfalls' => 0];
        $open = DB::table('legal_settlements as s')->join('legal as l', 'l.id', '=', 's.legal_id')
            ->whereIn('s.status', [self::ACTIVE, self::COMPLETED])->whereNull('l.closed_at')->select('s.*')->get();
        foreach ($open as $settlement) {
            $settlement = self::refresh($settlement);
            $counts['refreshed']++;
            if ((int) $settlement->status !== self::ACTIVE) {
                continue;
            }
            $legal = Legal::find($settlement->legal_id);
            foreach (self::schedule($settlement, $legal->tenant_contract_id)['rows'] as $r) {
                if ($r->status === 'overdue' && !$r->overdue_alerted_at) {
                    DB::table('legal_settlement_installments')->where('id', $r->id)->update(['overdue_alerted_at' => now()]);
                    self::notify($legal, 'legal_settlement_overdue',
                        ['amount' => numberFormat($r->amount - $r->paid), 'due_date' => date('d/m/Y', strtotime($r->due_date))], 'Settlement installment overdue');
                    $counts['overdue_alerts']++;
                }
            }
        }
        $closed = Legal::whereNotNull('closed_at')->whereIn('close_reason', ['paid_in_full', 'written_off'])
            ->whereIn('id', DB::table('legal_settlements')->where('status', self::COMPLETED)->whereNull('shortfall_alerted_at')->pluck('legal_id'))->get();
        foreach ($closed as $legal) {
            $before = DB::table('legal_settlements')->where('legal_id', $legal->id)->whereNotNull('shortfall_alerted_at')->count();
            self::shortfallCheck($legal);
            $counts['shortfalls'] += DB::table('legal_settlements')->where('legal_id', $legal->id)->whereNotNull('shortfall_alerted_at')->count() - $before;
        }
        return $counts;
    }

    /** In-app notification + system template to the case lawyer and PLM (backoffice managers). */
    public static function notify(Legal $legal, $templateKey, array $extra, $text, $toLawyer = true, $toPlm = true)
    {
        $users = collect();
        if ($toPlm) {
            $users = $users->merge(User::role(['backoffice_manager'])->get());
        }
        if ($toLawyer && $legal->lawyer_user_id) {
            $users = $users->merge(User::where('id', $legal->lawyer_user_id)->get());
        }
        $users = $users->unique('id')->values();
        if (!$users->count()) {
            return;
        }
        try {
            $n = clone $legal;
            $n->text = $text . ' - ' . ($legal->tenantContract->tenant_contract_no ?? '');
            $n->href = route('legal.case', $legal->id);
            Notification::send($users, new LegalNotification($n));
        } catch (\Exception $e) {
            Log::warning('Legal notification failed for case ' . $legal->id . ': ' . $e->getMessage());
        }
        LegalMessages::toUsers($templateKey, $users->all(), $legal->tenantContract, $extra);
    }
}
