<?php

namespace Tests\Unit;

use PHPUnit\Framework\TestCase;
use Modules\Masters\Entities\Building;

class BuildingOwnershipTypeTest extends TestCase
{
    public function test_ownership_types_in_order_with_individual_default()
    {
        $this->assertSame([1 => 'MERA', 2 => 'Government', 3 => 'Family Property', 4 => 'Individual'], Building::ownershipTypes());
        $this->assertSame(4, Building::OWNERSHIP_INDIVIDUAL);
    }

    public function test_ownership_type_name()
    {
        $b = new Building();
        $b->building_ownership_type = 1;
        $this->assertSame('MERA', $b->ownership_type_name);

        $b->building_ownership_type = '3';
        $this->assertSame('Family Property', $b->ownership_type_name);

        $b->building_ownership_type = null;
        $this->assertSame('', $b->ownership_type_name);

        $b->building_ownership_type = 9;
        $this->assertSame('', $b->ownership_type_name);
    }

    public function test_status_name_shows_handed_over()
    {
        $b = new Building();
        $b->building_status = 1;
        $b->building_handed_over = false;
        $this->assertSame('Active', $b->building_status_name);

        $b->building_status = 0;
        $this->assertSame('Inactive', $b->building_status_name);

        $b->building_handed_over = true;
        $this->assertSame('Handed Over', $b->building_status_name);

        // re-activated building: the flag no longer wins
        $b->building_status = 1;
        $this->assertSame('Active', $b->building_status_name);
    }
}
