<?php

namespace Modules\MobileApi\Tests\Feature;

use Modules\Masters\Services\AreBuildings;
use Modules\MobileApi\Tests\TestCase;

class BuildingApiTest extends TestCase
{
    public function test_my_buildings_with_counts(): void
    {
        $are = $this->actingAsAre();
        $r = $this->getJson('/api/v1/buildings')->assertOk()
            ->assertJsonStructure(['data' => [['id', 'code', 'name', 'units', 'unassigned_complaints', 'my_complaints', 'pending_inspections', 'open_onboardings']]]);
        $this->assertCount(AreBuildings::forUser($are)->count(), $r->json('data'));
        foreach ($r->json('data')[0] as $k => $v) {
            if (in_array($k, ['units', 'unassigned_complaints', 'my_complaints', 'pending_inspections', 'open_onboardings'], true)) {
                $this->assertIsInt($v, $k);
            }
        }
    }
}
