<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class IsTeacher
{
    public function handle(Request $request, Closure $next)
    {
        if (!$request->user() instanceof \App\Models\Teacher) {
            return response()->json([
                'message' => 'Unauthorized. Teacher access required.',
            ], 403);
        }

        return $next($request);
    }
}
