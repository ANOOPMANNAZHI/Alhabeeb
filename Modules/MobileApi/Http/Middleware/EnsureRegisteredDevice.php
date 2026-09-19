<?php

namespace Modules\MobileApi\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Modules\MobileApi\Exceptions\DeviceException;
use Modules\MobileApi\Services\DevicePolicy;

class EnsureRegisteredDevice
{
    public function handle(Request $request, Closure $next)
    {
        $uid = (string) $request->header('X-Device-Id', '');
        if ($uid === '' || !$request->user()) {
            return response()->json(['message' => 'Device id missing.', 'code' => 'device_not_registered'], 403);
        }
        try {
            $device = DevicePolicy::authorise($request->user(), $uid, ['app_version' => $request->header('X-App-Version')]);
        } catch (DeviceException $e) {
            return response()->json(['message' => $e->getMessage(), 'code' => $e->code()], 403);
        }
        $request->attributes->set('mobile_device', $device);
        return $next($request);
    }
}
