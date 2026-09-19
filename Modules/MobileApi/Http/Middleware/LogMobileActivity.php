<?php

namespace Modules\MobileApi\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

/** Every successful API write goes to the activity log as log_name "mobile". */
class LogMobileActivity
{
    public function handle(Request $request, Closure $next)
    {
        $response = $next($request);
        if (!$request->isMethod('GET') && $response->getStatusCode() < 400 && $request->user()) {
            $device = $request->attributes->get('mobile_device');
            activity('mobile')->causedBy($request->user())
                ->withProperties([
                    'route' => $request->route()?->getName(),
                    'device_uid' => $device?->device_uid,
                    'app_version' => $device?->app_version,
                    'params' => $request->route()?->parameters(),
                ])
                ->log($request->method() . ' ' . $request->path());
        }
        return $response;
    }
}
