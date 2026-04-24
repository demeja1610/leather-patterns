<?php

declare(strict_types=1);

use Illuminate\Support\Facades\Route;

Route::prefix('patterns')
    ->name('pattern.')
    ->group(callback: function (): void {
        Route::get('/', \App\Http\Controllers\Pattern\Api\v1\GetCursorPaginatedController::class)
            ->name(name: 'list');
    });
