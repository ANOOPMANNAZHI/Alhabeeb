<?php

namespace Modules\BackOffice\Services;

use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Cache;

/**
 * Due / Received / Balance / Not Paid From / Last Received / Paid Till per tenant
 * contract for the Receivables Follow-up screen.
 *
 * Dues: rent that fell due up to the cutoff, the end of the month two months before
 * the month picked (October -> 31 August), from the shared RentDues / RentLedger
 * calculation (same rules as the Tenant Aging Report). Payments: every rent receipt
 * up to today, so a tenant who has paid since the cutoff is not chased. Only
 * agreements still owing at least 30 are kept (never-paid included). Left out:
 * company properties (EXCLUDED_BUILDINGS) and any tenant + unit with a legal case
 * (the Normal Management report's rule), user 04/10/2026.
 * Cached per cutoff and day (Laravel 5.7: TTL in minutes). PHP < 7.4: no arrow functions.
 */
class ReceivableFigures
{
    const TTL_MINUTES = 1440;
    const BUSY_MINUTES = 2;
    const MONTHS_BACK = 2;
    const MIN_BALANCE = 30; // tiny leftovers are not chased
    // company properties, nobody to chase: Al Habib Office Leases, In House Properties, Left & Other Bldgs
    const EXCLUDED_BUILDINGS = [292, 414, 415];

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

    /** v7: RentLedger figures, payments up to the day they were computed; rent; company buildings and legal out. */
    public static function key($asOn, $today = null)
    {
        return 'receivable_followup:v7:' . $asOn . ':' . Carbon::parse($today ?: Carbon::today())->format('Y-m-d');
    }

    /**
     * Pure. $dues: RentDues::forDate() result. Keeps contracts owing at least MIN_BALANCE.
     * Returns [contractId => ['due', 'received', 'balance', 'not_paid_from', 'last_received', 'last_amount', 'paid_till', 'start', 'rent']].
     */
    public static function fromDues(array $dues)
    {
        $out = [];
        foreach ($dues as $id => $d) {
            $l = $d->ledger;
            if ($l['balance'] < self::MIN_BALANCE) {
                continue;
            }
            $out[(int) $id] = [
                'due'           => $l['due'],
                'received'      => $l['received'],
                'balance'       => $l['balance'],
                'not_paid_from' => $l['not_paid_from'],
                'last_received' => $d->last_received ? Carbon::parse($d->last_received)->format('Y-m-d') : null,
                'last_amount'   => $d->last_amount !== null ? round((float) $d->last_amount, 3) : null,
                'paid_till'     => $d->paid_till ? Carbon::parse($d->paid_till)->format('Y-m-d') : null,
                'start'         => Carbon::parse($d->eff)->format('Y-m-d'),
                'rent'          => isset($d->rent) ? round((float) $d->rent, 3) : null,
            ];
        }
        return $out;
    }

    /** Dues up to the cutoff, payments up to today. */
    public function compute($asOn)
    {
        $today = Carbon::today()->format('Y-m-d');
        $rows = self::fromDues(app(RentDues::class)->forDate($asOn, max($asOn, $today)));
        foreach (self::notFollowedUp(array_keys($rows)) as $id) {
            unset($rows[(int) $id]);
        }
        return $rows;
    }

    /** Of these contracts, the ones in a company building or whose tenant + unit has a legal case. */
    public static function notFollowedUp(array $ids)
    {
        if (!$ids) {
            return [];
        }
        return DB::table('tenant_contracts as tc')
            ->whereIn('tc.id', $ids)
            ->where(function ($w) {
                $w->whereIn('tc.building_id', self::EXCLUDED_BUILDINGS)
                  ->orWhereExists(function ($e) {
                      $e->select(DB::raw(1))->from('legal as l')
                        ->whereColumn('l.tenant_id', 'tc.tenant_id')
                        ->whereColumn('l.unit_id', 'tc.unit_id');
                  });
            })
            ->pluck('tc.id')->all();
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
