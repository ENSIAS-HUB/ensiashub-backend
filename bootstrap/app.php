<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Http\Request;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        api: __DIR__.'/../routes/api.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware) {
        $middleware->alias([
            'group.member'       => \App\Http\Middleware\EnsureGroupMember::class,
            'ensure.role'        => \App\Http\Middleware\EnsureRole::class,
            'drive.access'       => \App\Http\Middleware\DriveAccessMiddleware::class,
            'token.query'        => \App\Http\Middleware\TokenFromQueryParam::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions) {
        // ✅ FIX : retourner JSON 401 au lieu de rediriger vers 'login'
        $exceptions->render(function (\Illuminate\Auth\AuthenticationException $e, Request $request) {
            return response()->json([
                'success' => false,
                'message' => 'Non authentifié.',
            ], 401);
        });
    })->create();