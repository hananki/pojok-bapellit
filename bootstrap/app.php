<?php

use App\Http\Middleware\AdminMiddleware;
use App\Http\Middleware\CekStatusAktif;
use App\Http\Middleware\RoleMiddleware; // <-- Pastikan ini ada
use App\Http\Middleware\TrackLoginDevice;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        commands: __DIR__.'/../routes/console.php',
        channels: __DIR__.'/../routes/channels.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware) {
        // Semua alias middleware dalam SATU blok
        $middleware->alias([
            'cek.status' => CekStatusAktif::class,
            'admin' => AdminMiddleware::class,
            'role' => RoleMiddleware::class, // <-- Pastikan ini ada
        ]);

        $middleware->append(TrackLoginDevice::class);

        // Trust proxies (buat di belakang Nginx/load balancer)
        $middleware->trustProxies(at: '*');
    })
    ->withExceptions(function (Exceptions $exceptions) {
        //
    })
    ->create();