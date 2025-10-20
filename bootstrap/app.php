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
    ->withMiddleware(function (Middleware $middleware) {
        // Register middleware aliases
        $middleware->alias([
            'admin' => \App\Http\Middleware\AdminMiddleware::class,
            'agent' => \App\Http\Middleware\AgentMiddleware::class,
            'role' => \App\Http\Middleware\RoleMiddleware::class,
            'activity.log' => \App\Http\Middleware\ActivityLogger::class,
        ]);
        
        // CORS is handled in Kernel.php for API routes
        
        // Configure Sanctum for API authentication
        // Note: Stateful configuration is now handled in config/sanctum.php
    })
    ->withExceptions(function (Exceptions $exceptions) {
        //
    })->create();
