<?php

namespace Modules\Masters\Services;

use App\User;
use Carbon\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

/**
 * The buildings an ARE is responsible for on a given day:
 * are_buildings (user, assign_from, assign_too) x preferred_buildings (building_id, assign_from, assign_to).
 * A null end date means open-ended. The desktop closes a single building by
 * dating preferred_buildings.assign_to, so both levels are checked.
 */
class AreBuildings
{
    public static function forUser(User $user, ?Carbon $on = null): Collection
    {
        $on = ($on ?? Carbon::today())->toDateString();

        return DB::table('are_buildings as ab')
            ->join('preferred_buildings as pb', 'pb.are_building_id', '=', 'ab.id')
            ->where('ab.user_id', $user->id)
            ->where(fn ($q) => $q->whereNull('ab.assign_from')->orWhereDate('ab.assign_from', '<=', $on))
            ->where(fn ($q) => $q->whereNull('ab.assign_too')->orWhereDate('ab.assign_too', '>=', $on))
            ->where(fn ($q) => $q->whereNull('pb.assign_from')->orWhereDate('pb.assign_from', '<=', $on))
            ->where(fn ($q) => $q->whereNull('pb.assign_to')->orWhereDate('pb.assign_to', '>=', $on))
            ->pluck('pb.building_id')
            ->map(fn ($id) => (int) $id)
            ->unique()
            ->values();
    }

    public static function covers(User $user, int $buildingId, ?Carbon $on = null): bool
    {
        return self::forUser($user, $on)->contains($buildingId);
    }
}
