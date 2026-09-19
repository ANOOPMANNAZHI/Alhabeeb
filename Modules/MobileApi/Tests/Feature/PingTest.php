<?php

namespace Modules\MobileApi\Tests\Feature;

use Modules\MobileApi\Tests\TestCase;

class PingTest extends TestCase
{
    public function test_ping_when_enabled(): void
    {
        $this->getJson('/api/v1/ping')->assertOk()->assertJson(['pong' => true]);
    }

    public function test_disabled_flag_returns_503(): void
    {
        config(['mobileapi.enabled' => false]);
        $this->getJson('/api/v1/ping')->assertStatus(503)->assertJson(['code' => 'disabled']);
    }
}
