<?php

namespace Modules\MobileApi\Tests\Feature;

use App\User;
use Modules\MobileApi\Entities\MobileDevice;
use Modules\MobileApi\Tests\TestCase;

class DeviceAdminTest extends TestCase
{
    public function test_admin_can_list_clear_and_block(): void
    {
        $are = $this->areUser();
        MobileDevice::updateOrCreate(['user_id' => $are->id], ['device_uid' => 'dev-Z', 'registered_at' => now()]);
        $are->createToken('dev-Z');
        $admin = User::where('username', 'admin')->firstOrFail();

        $this->actingAs($admin)->get(route('mobile-devices.index'))->assertOk()->assertSee('dev-Z');

        $this->actingAs($admin)->post(route('mobile-devices.block', $are))->assertRedirect();
        $this->assertNotNull(MobileDevice::where('user_id', $are->id)->value('blocked_at'));
        $this->assertSame(0, $are->tokens()->count());

        $this->actingAs($admin)->post(route('mobile-devices.unblock', $are))->assertRedirect();
        $this->assertNull(MobileDevice::where('user_id', $are->id)->value('blocked_at'));

        $this->actingAs($admin)->post(route('mobile-devices.clear', $are))->assertRedirect();
        $this->assertDatabaseMissing('mobile_devices', ['user_id' => $are->id]);
    }

    public function test_requires_permission(): void
    {
        $this->actingAs($this->areUser())->get(route('mobile-devices.index'))->assertStatus(403);
    }
}
