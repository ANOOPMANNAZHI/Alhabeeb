<?php

namespace Modules\BackOffice\Services;

use Carbon\Carbon;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

/**
 * Due / Received / Balance / Last Received / Paid Till per tenant contract for the
 * Receivables Follow-up screen, all counted up to a cutoff date: the end of the month
 * two months before the month picked (October -> 31 August).
 *
 * Due follows Receivable Report v2's rules, but only up to the cutoff: rent x whole
 * months from the effective month to the cutoff (or the contract end), capped at the
 * contract value; a contract terminated by the cutoff uses v2's termination rule.
 * Received = rent receipts dated on or before the cutoff. Only agreements that have
 * paid at least once and still owe at the cutoff are kept; legal cases are left out.
 * Cached per cutoff date (Laravel 5.7: TTL in minutes). PHP < 7.4: no arrow functions.
 */
class ReceivableFigures
{
    const TTL_MINUTES = 1440;
    const BUSY_MINUTES = 2;
    const MONTHS_BACK = 2;

    public static function lastMonth($today = null)
    {
        $d = Carbon::parse($today ?: Carbon::today())->startOfMonth()->subMonth();
        return [(int) $d->month, (int) $d->year];
    }

    /** The cutoff for a picked month: last day of the month two months earlier, never after today. */
    public static function asOnDate($month, $year, $today = null)
    {
        $today = Carbon::parse($today ?: Carbon::today())->startOfDay();
        $end = Carbon::create((int) $year, (int) $month, 1, 0, 0, 0)->subMonthsNoOverflow(self::MONTHS_BACK)->endOfMonth()->startOfDay();
        return ($end->gt($today) ? $today : $end)->format('Y-m-d');
    }

    /** v4: figures counted up to the cutoff (entries cached before that are ignored and rebuilt). */
    public static function key($asOn)
    {
        return 'receivable_followup:v4:' . $asOn;
    }

    /**
     * Pure. Rent due by $cutoff for one contract row (eff, start, vto, rent, value, term_date).
     */
    public static function dueAsOf($c, $cutoff)
    {
        $rent = (float) $c->rent;
        $value = (float) $c->value;
        $termDate = $c->term_date ? substr((string) $c->term_date, 0, 10) : null;

        if ($termDate !== null && $termDate <= $cutoff) {
            if ($termDate === substr((string) $c->vto, 0, 10)) {
                return round($value, 3);
            }
            $start = Carbon::parse($c->start ?: $c->eff);
            $days = Carbon::parse($termDate)->diffInDays($start, true) - 1;
            return (float) round($days * ($rent / 30));
        }

        $eff = Carbon::parse($c->eff);
        $end = Carbon::parse($cutoff);
        if ($c->vto && Carbon::parse($c->vto)->lt($end)) {
            $end = Carbon::parse($c->vto);
        }
        $months = ($end->year - $eff->year) * 12 + ($end->month - $eff->month) + 1;
        if ($months <= 0) {
            return 0.0;
        }
        $due = $months * $rent;
        if ($value > 0 && $due > $value) {
            $due = $value;
        }
        return round($due, 3);
    }

    /**
     * Pure. $rows: contract rows (see compute()) with receipts up to the cutoff already summed.
     * Returns [contractId => ['due', 'received', 'balance', 'last_received', 'last_amount', 'paid_till']].
     */
    public static function build(array $rows, $cutoff)
    {
        $out = [];
        foreach ($rows as $r) {
            if (substr((string) $r->eff, 0, 10) > $cutoff) {
                continue; // not started by the cutoff
            }
            $due = self::dueAsOf($r, $cutoff);
            $received = round((float) $r->received, 3);
            $balance = round($due - $received, 3);
            if ($balance <= 0) {
                continue;
            }
            $out[(int) $r->id] = [
                'due'           => $due,
                'received'      => $received,
                'balance'       => $balance,
                'last_received' => $r->last_received ? Carbon::parse($r->last_received)->format('Y-m-d') : null,
                'last_amount'   => $r->last_amount !== null ? round((float) $r->last_amount, 3) : null,
                'paid_till'     => $r->paid_till ? Carbon::parse($r->paid_till)->format('Y-m-d') : null,
            ];
        }
        return $out;
    }

    /** Approved contracts in active buildings, not legal, started by the cutoff, with rent receipts up to it. */
    public function compute($asOn)
    {
        $rows = DB::select("
            SELECT tc.id,
                   tc.tenant_contract_effective_date::date AS eff,
                   tc.tenant_contract_start_date::date      AS start,
                   tc.tenant_contract_valid_to_date::date   AS vto,
                   tc.tenant_contract_rent                  AS rent,
                   tc.tenant_contract_value                 AS value,
                   ter.term_date,
                   r.received, r.paid_till, r.last_received,
                   (SELECT sum(rl.receipts_generation_amt) FROM receipts_generation rl
                     WHERE rl.tenant_contract_id = tc.id AND rl.receipts_generation_type = '0' AND rl.deleted_at IS NULL
                       AND rl.receipts_generation_receipt_date::date = r.last_received::date) AS last_amount
              FROM tenant_contracts tc
              JOIN buildings b ON b.id = tc.building_id AND b.building_status = '1'
              JOIN (SELECT tenant_contract_id,
                           sum(receipts_generation_amt)          AS received,
                           max(receipts_generation_eff_to)        AS paid_till,
                           max(receipts_generation_receipt_date)  AS last_received
                      FROM receipts_generation
                     WHERE receipts_generation_type = '0' AND deleted_at IS NULL
                       AND receipts_generation_receipt_date::date <= ?::date
                     GROUP BY tenant_contract_id) r ON r.tenant_contract_id = tc.id
              LEFT JOIN (SELECT contract_id, max(termination_date)::date AS term_date
                           FROM termination WHERE work_flow_processes_code = '505'
                          GROUP BY contract_id) ter ON ter.contract_id = tc.id
             WHERE tc.work_flow_processes_code = 108
               AND tc.tenant_contract_effective_date::date <= ?::date
               AND NOT EXISTS (SELECT 1 FROM legal l WHERE l.tenant_contract_id = tc.id)
        ", [$asOn, $asOn]);
        return self::build($rows, $asOn);
    }

    /**
     * Recompute and cache. On failure the exception propagates and the old cache entry stays.
     * One calculation per cutoff at a time (the file cache has no locks in 5.7, so a
     * short-lived ":busy" marker set with add() stands in); others get FiguresBusy.
     */
    public function refresh($asOn)
    {
        $busy = self::key($asOn) . ':busy';
        if (!Cache::add($busy, 1, self::BUSY_MINUTES)) {
            throw new FiguresBusy('Figures for ' . $asOn . ' are being calculated.');
        }
        try {
            $data = ['computed_at' => Carbon::now()->format('Y-m-d H:i:s'), 'rows' => $this->compute($asOn)];
            Cache::put(self::key($asOn), $data, self::TTL_MINUTES);
            return $data;
        } finally {
            Cache::forget($busy);
        }
    }

    public function cached($asOn)
    {
        $data = Cache::get(self::key($asOn));
        return is_array($data) ? $data : null;
    }

    public function get($asOn)
    {
        $data = $this->cached($asOn);
        return $data ?: $this->refresh($asOn);
    }
}
