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
}
