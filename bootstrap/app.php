<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__ . '/../routes/web.php',
        commands: __DIR__ . '/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        $middleware->encryptCookies(except: [
            'downloadComplete',
        ]);
        $middleware->alias([
            'auth.interact' => \App\Http\Middleware\RequireAuthForInteraction::class,
            'tier' => \App\Http\Middleware\RequireTier::class,
            'admin' => \App\Http\Middleware\EnsureUserIsAdmin::class,
        ]);
        $middleware->validateCsrfTokens(except: [
            '/whatsapp/webhook',   // ← add this line
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        //
    })->create();
