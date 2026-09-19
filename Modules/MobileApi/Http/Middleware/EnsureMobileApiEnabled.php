<?php

namespace Modules\MobileApi\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class EnsureMobileApiEnabled
{
    public function handle(Request $request, Closure $next)
    {
        if (!config('mobileapi.enabled')) {
            return response()->json(['message' => 'Mobile API disabled', 'code' => 'disabled'], 503);
        }
        return $next($request);
    }
}
