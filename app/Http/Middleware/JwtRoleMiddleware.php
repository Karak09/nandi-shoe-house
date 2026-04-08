<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class JwtRoleMiddleware
{
    public function handle(Request $request, Closure $next, string $roleIds): Response
    {
        // 1. Check if user is authenticated via JWT
        if (!auth('api')->check()) {
            return response()->json(['message' => 'Session expired. Please log in again.'], 401);
        }

        $user = auth('api')->user();

        // 2. Check if the user's role matches any of the required role IDs
        // We explode the string so we can pass multiple roles like jwt.role:0,1
        $allowedRoles = explode(',', $roleIds);
        
        if (!in_array((string)$user->user_type_id, $allowedRoles)) {
            return response()->json(['message' => 'Forbidden: You do not have permission to access this module.'], 403);
        }

        return $next($request);
    }
}