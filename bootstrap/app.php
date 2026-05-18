<?php

use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\RateLimiter;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware) {
        // Login: máximo 5 tentativas por minuto por IP
        RateLimiter::for('login', function (Request $request) {
            return Limit::perMinute(5)->by($request->ip());
        });

        // Cadastro/envio de código: máximo 3 por minuto por IP
        RateLimiter::for('send-code', function (Request $request) {
            return Limit::perMinute(3)->by($request->ip());
        });

        // Geral: máximo 60 requisições por minuto por IP
        RateLimiter::for('global', function (Request $request) {
            return Limit::perMinute(60)->by($request->ip());
        });

        $middleware->web(append: [
            \App\Http\Middleware\SecurityHeaders::class,
            'throttle:global',
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions) {
        //
    })->create();
