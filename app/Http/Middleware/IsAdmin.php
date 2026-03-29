<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class IsAdmin
{
    public function handle(Request $request, Closure $next)
    {
        // Verify the authenticated user is an Admin model instance
        if (!$request->user() instanceof \App\Models\Admin) {
            return response()->json([
                'message' => 'Unauthorized. Admin access required.',
            ], 403);
        }

        // Verify the token was issued with the admin role ability
        if (!$request->user()->tokenCan('role:admin')) {
            return response()->json([
                'message' => 'Unauthorized. Invalid token abilities.',
            ], 403);
        }

        return $next($request);
    }
}
