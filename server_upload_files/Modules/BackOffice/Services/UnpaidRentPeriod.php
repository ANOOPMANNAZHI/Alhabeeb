<?php

namespace Modules\BackOffice\Services;

use Carbon\Carbon;

/**
 * Describes which rent months are still unpaid on a contract, for display
 * under the pre-filled "Rent" charge on the Vacating Unit Inspection so the
 * inspector can verify the figure. Rent is paid up to `paidTill` (the last
 * approved rent receipt's effective-to date); everything from the next day
 * to the termination date is unpaid, a part month counting as a month.
 * Pure PHP (no DB); runs on PHP < 7.4.
 */
class UnpaidRentPeriod
{
    /**
     * @param  string|null $effectiveDate  contract start (used when never paid)
     * @param  string|null $paidTill       last receipt effective-to date, null if none
     * @param  string|null $terminationDate
     * @return array ['from' => 'Sep 2025', 'to' => 'Aug 2026', 'months' => 12, 'label' => '...']
     */
    public static function describe($effectiveDate, $paidTill, $terminationDate)
    {
        $empty = ['from' => '', 'to' => '', 'months' => 0, 'label' => ''];
        if (empty($terminationDate) || (empty($paidTill) && empty($effectiveDate))) {
            return $empty;
        }

        $end   = Carbon::parse($terminationDate)->startOfDay();
        $start = !empty($paidTill)
            ? Carbon::parse($paidTill)->startOfDay()->addDay()
            : Carbon::parse($effectiveDate)->startOfDay();

        $paidNote = !empty($paidTill)
            ? 'paid till ' . Carbon::parse($paidTill)->format('d/m/Y')
            : 'no rent receipt on this contract';

        if ($start->gt($end)) {
            return array_merge($empty, ['label' => 'Rent ' . $paidNote]);
        }

        // count calendar months touched by the unpaid period
        $months = ($end->year - $start->year) * 12 + ($end->month - $start->month) + 1;
        $from   = $start->format('M Y');
        $to     = $end->format('M Y');
        $range  = $months === 1 ? $from : $from . ' - ' . $to;

        return [
            'from'   => $from,
            'to'     => $to,
            'months' => $months,
            'label'  => sprintf('Unpaid: %s (%d month%s) · %s', $range, $months, $months === 1 ? '' : 's', $paidNote),
        ];
    }
}
