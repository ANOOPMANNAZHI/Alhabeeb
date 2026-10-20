<?php

namespace Modules\BackOffice\Services;

use Illuminate\Support\Facades\DB;

/**
 * Rent outstanding of a tenant, for the termination screens (user, 06/10/2026):
 *   - the contract being terminated, as on its termination date (RentOutstanding);
 *   - plus every EXPIRED contract of the tenant on any unit (ended or terminated, not the
 *     one being terminated, not active), at the Rent Receivable V2 figure (RentDues /
 *     RentLedger) - legal cases included and flagged, since legal follows termination.
 * Contracts in buildings that are no longer active are left out, as in V2.
 * On the contract being terminated a rent receipt counts once created, also while it waits
 * for approval (RentOutstanding::CREATED_APPROVAL, user 20/10/2026).
 * Runs on PHP < 7.4.
 */
class TenantRentOutstanding
{
    /**
     * @param  object $contract     the contract being terminated (TenantContract)
     * @param  string $terminationDate
     * @return array ['total', 'current' => row, 'expired' => [row...], 'expired_total', 'same_unit_total', 'paid_till']
     *               row: contract_no, building, unit, from, to, outstanding, unpaid, in_legal, same_unit
     */
    public static function forTermination($contract, $terminationDate)
    {
        $today = date('Y-m-d');
        $current = RentOutstanding::forContract($contract->id, $terminationDate ?: null, RentOutstanding::CREATED_APPROVAL);
        $paidTill = DB::table('receipts_generation')->where('tenant_contract_id', $contract->id)
            ->where('receipts_generation_type', 0)
            ->whereIn('receipts_generation_approval_status', RentOutstanding::CREATED_APPROVAL)
            ->where('receipts_generation_status', '<>', RentOutstanding::STATUS_CANCELLED)
            ->whereNull('deleted_at')->max('receipts_generation_eff_to');
        $from = $contract->tenant_contract_effective_date ?: $contract->tenant_contract_start_date;
        $to = $current['as_of'];
        $currentRow = [
            'contract_no' => $contract->tenant_contract_no,
            'building'    => optional($contract->building)->building_name,
            'unit'        => optional($contract->Unit)->unit_no,
            'from'        => self::ymd($from),
            'to'          => $to,
            'outstanding' => (float) $current['outstanding'],
            'unpaid'      => $current['outstanding'] > 0 ? self::unpaid($from, $paidTill, $to) : '',
            'in_legal'    => DB::table('legal')->where('tenant_contract_id', $contract->id)->exists(),
            'same_unit'   => true,
        ];

        $expired = [];
        $expiredTotal = 0.0;
        $sameUnitTotal = 0.0;
        $dues = app(RentDues::class)->forDate($today, $today, ['tenant_id' => $contract->tenant_id, 'include_legal' => 1]);
        foreach ($dues as $id => $r) {
            $end = $r->term_date ?: $r->vto;
            if ((int) $id === (int) $contract->id || !$end || $end >= $today) {
                continue; // the contract being terminated, or still running
            }
            $balance = (float) $r->ledger['balance'];
            if ($balance <= 0) {
                continue;
            }
            $sameUnit = (int) DB::table('tenant_contracts')->where('id', $id)->value('unit_id') === (int) $contract->unit_id;
            $expired[] = [
                'contract_no' => $r->contract_no,
                'building'    => $r->building_name,
                'unit'        => $r->unit_code,
                'from'        => self::ymd($r->eff ?: $r->start),
                'to'          => self::ymd($end),
                'outstanding' => $balance,
                'unpaid'      => self::unpaid($r->eff ?: $r->start, $r->paid_till, $end),
                'in_legal'    => (bool) $r->in_legal,
                'same_unit'   => $sameUnit,
            ];
            $expiredTotal += $balance;
            if ($sameUnit) {
                $sameUnitTotal += $balance;
            }
        }
        usort($expired, function ($a, $b) { return strcmp($a['from'], $b['from']); });

        return [
            'total'           => round($currentRow['outstanding'] + $expiredTotal, 3),
            'current'         => $currentRow,
            'expired'         => $expired,
            'expired_total'   => round($expiredTotal, 3),
            'same_unit_total' => round($sameUnitTotal, 3),
            'paid_till'       => self::ymd($paidTill),
        ];
    }

    private static function unpaid($from, $paidTill, $to)
    {
        $p = UnpaidRentPeriod::describe($from, $paidTill, $to);
        return $p['months'] > 0 ? sprintf('%s (%d month%s)', $p['from'] === $p['to'] ? $p['from'] : $p['from'] . ' - ' . $p['to'],
            $p['months'], $p['months'] === 1 ? '' : 's') : '';
    }

    private static function ymd($value)
    {
        return $value ? date('Y-m-d', strtotime((string) $value)) : null;
    }
}
