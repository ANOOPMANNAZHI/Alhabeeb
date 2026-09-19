<?php

namespace Modules\MobileApi\Http\Controllers;

use App\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;
use Modules\Masters\Entities\Building;
use Modules\Masters\Services\AreBuildings;
use Modules\MobileApi\Exceptions\DeviceException;
use Modules\MobileApi\Http\Requests\LoginRequest;
use Modules\MobileApi\Http\Resources\BuildingResource;
use Modules\MobileApi\Http\Resources\UserResource;
use Modules\MobileApi\Services\DevicePolicy;

class AuthController extends ApiController
{
    public function login(LoginRequest $request)
    {
        if (!Auth::validate(['username' => $request->username, 'password' => $request->password])) {
            throw ValidationException::withMessages(['username' => ['Invalid username or password.']]);
        }
        /** @var User $user */
        $user = User::where('username', $request->username)->firstOrFail();

        if (!$user->can('mobile_app_login')) {
            return $this->error('forbidden', 'Your account is not enabled for the mobile app.', 403);
        }

        try {
            $device = DevicePolicy::authorise($user, $request->device_uid, $request->only('device_name', 'platform', 'app_version'));
        } catch (DeviceException $e) {
            return $this->error($e->code(), $e->getMessage(), 403);
        }

        $user->tokens()->where('name', $device->device_uid)->delete();
        $token = $user->createToken($device->device_uid)->plainTextToken; // no expiry

        activity('mobile')->causedBy($user)->withProperties(['device_uid' => $device->device_uid, 'app_version' => $device->app_version])->log('login');

        return response()->json([
            'token' => $token,
            'user' => new UserResource($user),
            'buildings' => BuildingResource::collection(Building::whereIn('id', AreBuildings::forUser($user))->orderBy('building_name')->get()),
        ]);
    }

    public function logout(Request $request)
    {
        $request->user()->currentAccessToken()->delete();
        return response()->json(['message' => 'Logged out.']);
    }

    public function me(Request $request)
    {
        $user = $request->user();
        return response()->json([
            'user' => new UserResource($user),
            'buildings' => BuildingResource::collection(Building::whereIn('id', AreBuildings::forUser($user))->orderBy('building_name')->get()),
            'device' => $request->attributes->get('mobile_device')?->only('device_uid', 'device_name', 'platform', 'app_version', 'registered_at'),
        ]);
    }
}
