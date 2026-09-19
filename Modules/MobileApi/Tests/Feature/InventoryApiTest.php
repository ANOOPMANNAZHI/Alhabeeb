<?php

namespace Modules\MobileApi\Tests\Feature;

use Modules\MobileApi\Tests\TestCase;

class InventoryApiTest extends TestCase
{
    public function test_lists_inventory_master(): void
    {
        $this->actingAsAre();
        $r = $this->getJson('/api/v1/inventories?per_page=5')->assertOk()->assertJsonStructure(['data' => [['id', 'name']], 'meta' => ['total']]);
        $name = $r->json('data.0.name');
        $this->getJson('/api/v1/inventories?q=' . urlencode(substr($name, 0, 3)))->assertOk()->assertJsonFragment(['name' => $name]);
    }
}
