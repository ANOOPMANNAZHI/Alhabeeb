<?php

namespace Modules\MobileApi\Tests\Feature;

use App\User;
use Illuminate\Support\Facades\Hash;
use Modules\MobileApi\Entities\MobileDevice;
use Modules\MobileApi\Tests\TestCase;

class AuthTest extends TestCase
{
    private function areWithPassword(): User
    {
        $user = $this->areUser();
        $user->password = Hash::make('secret123');
        $user->save();
        MobileDevice::where('user_id', $user->id)->delete();
        return $user;
    }

    private function login(User $user, string $device = 'dev-A'): \Illuminate\Testing\TestResponse
    {
        return $this->postJson('/api/v1/auth/login', [
            'username' => $user->username, 'password' => 'secret123',
            'device_uid' => $device, 'device_name' => 'Pixel', 'platform' => 'android', 'app_version' => '1.0.0',
        ]);
    }

    public function test_first_login_binds_device_and_returns_token(): void
    {
        $user = $this->areWithPassword();
        $this->login($user)->assertOk()->assertJsonStructure(['token', 'user' => ['id', 'username'], 'buildings']);
        $this->assertDatabaseHas('mobile_devices', ['user_id' => $user->id, 'device_uid' => 'dev-A']);
    }

    public function test_other_device_is_refused(): void
    {
        $user = $this->areWithPassword();
        $this->login($user, 'dev-A')->assertOk();
        $this->login($user, 'dev-B')->assertStatus(403)->assertJson([
            'code' => 'device_not_registered',
            'message' => 'This device is not registered. Please contact the administrator.',
        ]);
        $this->assertDatabaseMissing('mobile_devices', ['device_uid' => 'dev-B']);
    }

    public function test_blocked_device_is_refused(): void
    {
        $user = $this->areWithPassword();
        $this->login($user, 'dev-A')->assertOk();
        MobileDevice::where('user_id', $user->id)->update(['blocked_at' => now()]);
        $this->login($user, 'dev-A')->assertStatus(403)->assertJson(['code' => 'device_blocked']);
    }

    public function test_wrong_password_and_missing_permission(): void
    {
        $user = $this->areWithPassword();
        $this->postJson('/api/v1/auth/login', ['username' => $user->username, 'password' => 'nope', 'device_uid' => 'dev-A'])
            ->assertStatus(422)->assertJson(['code' => 'validation']);

        $clerk = User::role('call_center')->firstOrFail();
        $clerk->password = Hash::make('secret123'); $clerk->save();
        $this->login($clerk)->assertStatus(403)->assertJson(['code' => 'forbidden']);
    }

    public function test_me_requires_matching_device_header_and_logout_revokes(): void
    {
        $user = $this->areWithPassword();
        $token = $this->login($user, 'dev-A')->json('token');
        $h = ['Authorization' => "Bearer $token", 'Accept' => 'application/json'];

        $this->withHeaders($h + ['X-Device-Id' => 'dev-A'])->getJson('/api/v1/auth/me')->assertOk()->assertJsonPath('user.id', $user->id);
        $this->withHeaders($h + ['X-Device-Id' => 'dev-B'])->getJson('/api/v1/auth/me')->assertStatus(403)->assertJson(['code' => 'device_not_registered']);

        $this->withHeaders($h + ['X-Device-Id' => 'dev-A'])->postJson('/api/v1/auth/logout')->assertOk();
        $this->assertDatabaseMissing('personal_access_tokens', ['tokenable_id' => $user->id, 'name' => 'dev-A']);
        app('auth')->forgetGuards(); // the test process caches the resolved user between requests
        $this->withHeaders($h + ['X-Device-Id' => 'dev-A'])->getJson('/api/v1/auth/me')->assertStatus(401);
    }
}
