<?php

namespace Tests\Unit;

use App\User;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Modules\Masters\Entities\AreBuildingAssign;
use Modules\Masters\Entities\Building;
use Modules\Masters\Services\AreBuildings;
use Tests\TestCase;

class AreBuildingsTest extends TestCase
{
    use DatabaseTransactions;

    private function makeAssignment(User $user, array $buildingIds, ?string $from, ?string $to): AreBuildingAssign
    {
        $a = AreBuildingAssign::create(['user_id' => $user->id, 'building_id' => $buildingIds[0], 'assign_from' => $from, 'assign_too' => $to]);
        foreach ($buildingIds as $b) {
            $a->buildingNames()->attach($b, ['assign_from' => $from, 'assign_to' => $to]);
        }
        return $a;
    }

    /** Four real building ids, so FK constraints hold. */
    private function buildings(): array
    {
        return Building::orderBy('id')->limit(4)->pluck('id')->map(fn ($id) => (int) $id)->all();
    }

    public function test_returns_buildings_from_current_assignments_only(): void
    {
        [$b1, $b2, $b3, $b4] = $this->buildings();
        $user = User::role('are')->firstOrFail();
        AreBuildingAssign::where('user_id', $user->id)->get()->each(fn ($a) => $a->buildingNames()->detach());
        AreBuildingAssign::where('user_id', $user->id)->delete();

        $this->makeAssignment($user, [$b1, $b2], '2020-01-01', null);          // open-ended
        $this->makeAssignment($user, [$b3], '2020-01-01', '2020-12-31');          // expired
        $this->makeAssignment($user, [$b2, $b4], '2099-01-01', null);              // future

        $ids = AreBuildings::forUser($user, Carbon::parse('2026-09-19'));

        $this->assertSame([$b1, $b2], $ids->sort()->values()->all());
        $this->assertTrue(AreBuildings::covers($user, $b1));
        $this->assertFalse(AreBuildings::covers($user, $b3));
    }

    /** The desktop closes a single building by dating preferred_buildings.assign_to; the header row stays open. */
    public function test_building_closed_on_pivot_is_excluded(): void
    {
        [$b1, $b2] = $this->buildings();
        $user = User::role('are')->firstOrFail();
        AreBuildingAssign::where('user_id', $user->id)->get()->each(fn ($a) => $a->buildingNames()->detach());
        AreBuildingAssign::where('user_id', $user->id)->delete();

        $a = $this->makeAssignment($user, [$b1, $b2], '2020-01-01', null);
        $a->buildingNames()->updateExistingPivot($b2, ['assign_to' => '2021-06-30']);

        $this->assertSame([$b1], AreBuildings::forUser($user, Carbon::parse('2026-09-19'))->all());
    }
}
