<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use App\Http\Middleware\CheckUserRole; // Import middleware kamu

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware) {
        // Tambahkan alias middleware di sini
        $middleware->alias([
            'role' => CheckUserRole::class, // Ini baris yang kamu butuhkan
        ]);

        // Anda juga bisa menambahkan middleware ke grup web atau api di sini jika diperlukan
        // $middleware->web(append: [
        //     \App\Http\Middleware\VerifyCsrfToken::class,
        // ]);
    })
    ->withExceptions(function (Exceptions $exceptions) {
        //
    })->create();