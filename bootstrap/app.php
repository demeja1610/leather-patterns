<?php

declare(strict_types=1);

use Illuminate\Http\Request;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;

return Application::configure(basePath: dirname(path: __DIR__))
    ->withRouting(
        web: __DIR__ . '/../routes/web.php',
        api: __DIR__ . '/../routes/api.php',
        commands: __DIR__ . '/../routes/console.php',
        health: '/up',
        apiPrefix: 'api',
    )
    ->withMiddleware(callback: function (Middleware $middleware): void {
        $middleware->redirectGuestsTo(
            redirect: fn(Request $request): string => route(name: 'page.auth.login'),
        );
    })
    ->withExceptions(using: function (Exceptions $exceptions): void {})->create();
