<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;

class CheckDeviceMiddleware
{
    public function handle(Request $request, Closure $next)
    {
        $user = auth('api')->user();

        if ($user) {
            $deviceId = $request->header('X-Device-Id');
            $devices = Cache::get('active_devices_' . $user->id, []);

            if (!$deviceId || !isset($devices[$deviceId])) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Device logged out'
                ], 401);
            }
        }

        return $next($request);
    }
}