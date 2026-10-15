<?php

namespace Modules\BackOffice\Services;

use App\User;
use Carbon\Carbon;
use Modules\Sales\Entities\TenantContract;

/**
 * Rent revisal before renewal (spec 2026-10-15). A contract can be revised when it is active, ends in
 * 90-120 days (both inclusive) and its renewal has not started (tenant_renewal_termination_status 0/1/4).
 * ARE scope follows Due for Renewal's areFilter: an ARE sees the buildings assigned to them, an ARE team
 * lead (as only role) their team's buildings, everyone else all buildings.
 */
class RentRevisal
{
    const MIN_DAYS = 90;
    const MAX_DAYS = 120;
    const OPEN_STATUSES = [0, 1, 4];

    public static function windowQuery($user)
    {
        $q = TenantContract::query()
            ->where('tenant_contracts.tenant_contract_status', 1)
            ->whereIn('tenant_contracts.tenant_renewal_termination_status', self::OPEN_STATUSES)
            ->whereDate('tenant_contracts.tenant_contract_valid_to_date', '>=', Carbon::today()->addDays(self::MIN_DAYS)->toDateString())
            ->whereDate('tenant_contracts.tenant_contract_valid_to_date', '<=', Carbon::today()->addDays(self::MAX_DAYS)->toDateString());
        return self::scope($q, $user);
    }

    public static function inWindow(TenantContract $c)
    {
        if ((int) $c->tenant_contract_status !== 1 || !in_array((int) $c->tenant_renewal_termination_status, self::OPEN_STATUSES, true)) {
            return false;
        }
        $end = Carbon::parse($c->tenant_contract_valid_to_date)->startOfDay();
        $days = Carbon::today()->diffInDays($end, false);
        return $days >= self::MIN_DAYS && $days <= self::MAX_DAYS;
    }

    public static function canRevise(TenantContract $c, $user)
    {
        return self::inWindow($c) && !$c->rentRevisal()->exists()
            && self::windowQuery($user)->where('tenant_contracts.id', $c->id)->exists();
    }

    public static function dueCount($user)
    {
        return self::windowQuery($user)->doesntHave('rentRevisal')->count();
    }

    /** Active users currently assigned as ARE of the contract's building. */
    public static function areUsers(TenantContract $c)
    {
        $building = $c->building;
        if (!$building) {
            return collect();
        }
        $ids = $building->areBuildings()->pluck('user_id')->filter()->unique()->values()->all();
        return User::whereIn('id', $ids)->where('user_type_status', 1)->get();
    }

    private static function scope($q, $user)
    {
        if (!$user) {
            return $q->whereRaw('1 = 0');
        }
        if ($user->hasRole('are')) {
            $uid = $user->id;
            $q->whereHas('building', function ($b) use ($uid) {
                $b->whereHas('areBuildings', function ($a) use ($uid) {
                    $a->where('user_id', $uid);
                });
            });
        } elseif (count($user->roles) == 1 && $user->hasRole('are_team_lead')) {
            $uid = $user->id;
            $q->whereHas('building', function ($b) use ($uid) {
                $b->whereHas('areBuildings', function ($a) use ($uid) {
                    $a->where(function ($w) use ($uid) {
                        $w->whereHas('user', function ($u) use ($uid) {
                            $u->whereHas('employee', function ($e) use ($uid) {
                                $e->where('head_user', $uid);
                            });
                        })->orWhere('user_id', $uid);
                    });
                });
            });
        }
        return $q;
    }
}
