<?php

declare(strict_types=1);

use Illuminate\Support\Facades\Route;

Route::middleware('throttle:60,1')->group(callback: function (): void {
    require_once __DIR__ . '/auth.php';

    Route::get('/', \App\Http\Controllers\Pattern\Web\v1\ListController::class)
        ->name(name: 'page.index');

    Route::prefix('/pattern/{id}')->group(function () {
        Route::get('/', \App\Http\Controllers\Pattern\Web\v1\SingleController::class)
            ->name(name: 'page.pattern.single');

        Route::middleware('auth')->group(function () {
            Route::post('toggle-like', \App\Http\Controllers\Pattern\Web\v1\ToggleLikeController::class)
                ->name('pattern.toggle-like');
        });
    });
});

require_once __DIR__ . '/admin.php';
