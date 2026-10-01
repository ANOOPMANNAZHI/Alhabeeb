<?php

namespace Modules\BackOffice\Services;

use Carbon\Carbon;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

/**
 * Due / Received / Balance / Last Received per tenant contract for the
 * Receivables Follow-up screen. Same base calculation as Receivable Report v2
 * (tenantrentreceivable), with v2's legal exclusion, one row per contract and
 * only contracts that still owe money. The base function takes ~15 s, so the
 * result is cached per as-on date (Laravel 5.7: TTL in minutes).
 * PHP < 7.4 in production: no arrow functions.
 */
class ReceivableFigures
{
    const TTL_MINUTES = 1440;
    const BUSY_MINUTES = 2;

    public static function lastMonth($today = null)
    {
        $d = Carbon::parse($today ?: Carbon::today())->startOfMonth()->subMonth();
        return [(int) $d->month, (int) $d->year];
    }

    /** Last day of the month, never later than today. */
    public static function asOnDate($month, $year, $today = null)
    {
        $today = Carbon::parse($today ?: Carbon::today())->startOfDay();
        $end = Carbon::create((int) $year, (int) $month, 1, 0, 0, 0)->endOfMonth()->startOfDay();
        return ($end->gt($today) ? $today : $end)->format('Y-m-d');
    }

    /** v2: rows carry paid_till (entries cached before that are ignored and rebuilt). */
    public static function key($asOn)
    {
        return 'receivable_followup:v2:' . $asOn;
    }

    /**
     * The list for a month shows agreements whose rent is paid till a date inside the
     * month two months earlier (October -> 1-31 August). Returns ['Y-m-d', 'Y-m-d'].
     */
    public static function paidTillWindow($month, $year)
    {
        $m = Carbon::create((int) $year, (int) $month, 1, 0, 0, 0)->subMonthsNoOverflow(2);
        return [$m->format('Y-m-d'), $m->copy()->endOfMonth()->format('Y-m-d')];
    }

    /**
     * Pure. $rows: objects from tenantrentreceivable(); $legalNos: contract numbers
     * with a legal record; $idsByNo: contract_no => tenant_contracts.id.
     */
    public static function build(array $rows, array $legalNos, array $idsByNo)
    {
        $legal = array_flip(array_map('strval', $legalNos));
        $out = [];
        foreach ($rows as $r) {
            $no = (string) $r->contract_no;
            if (isset($legal[$no]) || !isset($idsByNo[$no])) {
                continue;
            }
            $balance = round((float) $r->netamtdue, 3);
            if ($balance <= 0) {
                continue;
            }
            $id = (int) $idsByNo[$no];
            if (isset($out[$id])) {
                continue;
            }
            $out[$id] = [
                'due'           => round((float) $r->totaldue, 3),
                'received'      => round((float) $r->amount_received, 3),
                'balance'       => $balance,
                'last_received' => $r->receipt_date ? Carbon::parse($r->receipt_date)->format('Y-m-d') : null,
                'paid_till'     => !empty($r->lastpaid_till) ? Carbon::parse($r->lastpaid_till)->format('Y-m-d') : null,
            ];
        }
        return $out;
    }

    public function compute($asOn)
    {
        $rows = DB::select('SELECT contract_no, totaldue, amount_received, netamtdue, receipt_date, lastpaid_till FROM tenantrentreceivable(?::date)', [$asOn]);

        $legalNos = DB::table('legal')
            ->join('tenant_contracts', 'tenant_contracts.id', '=', 'legal.tenant_contract_id')
            ->pluck('tenant_contracts.tenant_contract_no')->all();

        $nos = [];
        foreach ($rows as $r) {
            $nos[(string) $r->contract_no] = true;
        }
        $idsByNo = [];
        foreach (array_chunk(array_keys($nos), 1000) as $chunk) {
            // a contract number used twice maps to the newest contract (highest id)
            foreach (DB::table('tenant_contracts')->whereIn('tenant_contract_no', $chunk)->orderBy('id')->get(['id', 'tenant_contract_no']) as $c) {
                $idsByNo[(string) $c->tenant_contract_no] = (int) $c->id;
            }
        }
        return self::build($rows, $legalNos, $idsByNo);
    }

    /**
     * Recompute and cache. On failure the exception propagates and the old cache entry stays.
     * One calculation per as-on date at a time (the file cache has no locks in 5.7, so a
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
