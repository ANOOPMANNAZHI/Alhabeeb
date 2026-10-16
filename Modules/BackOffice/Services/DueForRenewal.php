<?php

namespace Modules\BackOffice\Services;

use App\Setting;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Modules\BackOffice\Entities\ViewDueRenewal;

/**
 * The Due for Renewal rows, shared by the list and the ARE dashboard "Contracts Expiring" card so their
 * numbers match: active contracts ending within the renewal_notification months, renewal not started.
 * Optional ?expdays=N narrows it to contracts ending from today up to N days ahead (the card's choice).
 */
class DueForRenewal
{
    public static function query(Request $request)
    {
        $months = Setting::where('configuration_settings', 'renewal_notification')->first()->configuration_value;
        $future = Carbon::now()->addMonths($months)->format('Y-m-d H:i:s');
        $q = ViewDueRenewal::areFilter()->filter($request)
            ->whereDate('tenant_contract_valid_to_date', '<', $future)
            ->where('tenant_contract_status', 1)
            ->where(function ($query) {
                $query->where('tenant_renewal_termination_status', 0)
                      ->where('tenant_renewal_termination_status', '!=', 2)
                      ->orWhere('tenant_renewal_termination_status', 4)
                      ->orWhere('tenant_renewal_termination_status', 1)
                      ->orWhere('tenant_renewal_termination_status', 5)
                      ->orWhere('tenant_renewal_termination_status', 6);
            });
        if ((int) $request->expdays > 0) {
            $q->whereDate('tenant_contract_valid_to_date', '>=', Carbon::today()->toDateString())
              ->whereDate('tenant_contract_valid_to_date', '<', Carbon::today()->addDays((int) $request->expdays)->toDateString());
        }
        return $q;
    }

    /** Number of contracts the list shows (the view repeats rows per ARE / renewal, so count distinct ids). */
    public static function count(Request $request)
    {
        return self::query($request)->distinct()->count('id');
    }
}
