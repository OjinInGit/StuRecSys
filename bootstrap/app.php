<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        api: __DIR__.'/../routes/api.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware){
        $middleware->alias([
            'is_admin' => \App\Http\Middleware\IsAdmin::class,
            'is_teacher' => \App\Http\Middleware\IsTeacher::class,
        ]);

        //
    })
    ->withExceptions(function (Exceptions $exceptions) {

    //Unauthenticated (no token)
    $exceptions->render(function (
        \Illuminate\Auth\AuthenticationException $e,
        \Illuminate\Http\Request $request
    ) {
        if ($request->expectsJson() || $request->is('api/*')) {
            return response()->json([
                'message' => 'Unauthenticated. Please log in.',
            ], 401);
        }
    });

    // Unauthorized (wrong role)
    $exceptions->render(function (
        \Illuminate\Auth\Access\AuthorizationException $e,
        \Illuminate\Http\Request $request
    ) {
        if ($request->expectsJson() || $request->is('api/*')) {
            return response()->json([
                'message' => 'Unauthorized. You do not have permission to perform this action.',
            ], 403);
        }
    });
})->create();
