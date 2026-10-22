<?php

use App\Http\Middleware\EnsureUserIsAdmin;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware) {
        $middleware->alias([
            'admin' => EnsureUserIsAdmin::class,
        ]);

        // Aplikasi diasumsikan berjalan di belakang reverse proxy / load balancer
        // (Nginx, Caddy, load balancer cloud). Lihat docs/DEPLOYMENT.md bagian "Proxy".
        $middleware->trustProxies(at: '*');

        // Webhook pembayaran diverifikasi lewat signature HMAC, bukan token CSRF.
        $middleware->validateCsrfTokens(except: ['webhooks/*']);
    })
    ->withExceptions(function (Exceptions $exceptions) {
        //
    })->create();
