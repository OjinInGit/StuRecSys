<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class IsTeacher
{
    public function handle(Request $request, Closure $next)
    {
        // Verify the authenticated user is a Teacher model instance
        if (!$request->user() instanceof \App\Models\Teacher) {
            return response()->json([
                'message' => 'Unauthorized. Teacher access required.',
            ], 403);
        }

        // Verify the token was issued with the teacher role ability
        if (!$request->user()->tokenCan('role:teacher')) {
            return response()->json([
                'message' => 'Unauthorized. Invalid token abilities.',
            ], 403);
        }

        return $next($request);
    }
}
