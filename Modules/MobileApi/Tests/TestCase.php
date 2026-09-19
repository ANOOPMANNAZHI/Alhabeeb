<?php

namespace Modules\MobileApi\Tests;

use App\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Laravel\Sanctum\Sanctum;
use Modules\MobileApi\Entities\MobileDevice;
use Tests\TestCase as BaseTestCase;

abstract class TestCase extends BaseTestCase
{
    use DatabaseTransactions;

    protected const DEVICE = 'test-device-0001';

    protected function setUp(): void
    {
        parent::setUp();
        config(['mobileapi.enabled' => true]);
    }

    /** An existing user with role `are` and at least one building (the DB copy has 26). */
    protected function areUser(): User
    {
        return User::role('are')->whereHas('areBuildings')->firstOrFail();
    }

    /** Authenticate as $user through Sanctum, with a registered device header. */
    protected function actingAsAre(?User $user = null): User
    {
        $user ??= $this->areUser();
        MobileDevice::query()->updateOrCreate(['user_id' => $user->id], [
            'device_uid' => self::DEVICE, 'device_name' => 'Test', 'platform' => 'android',
            'app_version' => '0.0.1', 'registered_at' => now(), 'last_seen_at' => now(),
        ]);
        Sanctum::actingAs($user, ['*'], 'sanctum');
        $this->withHeaders(['Accept' => 'application/json', 'X-Device-Id' => self::DEVICE]);
        return $user;
    }
}
