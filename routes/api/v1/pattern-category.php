<?php

declare(strict_types=1);

use Illuminate\Support\Facades\Route;

Route::prefix('pattern-categories')
    ->name('pattern-categories.')
    ->group(callback: function (): void {
        Route::get('all', \App\Http\Controllers\PatternCategory\Api\v1\GetAllController::class)
            ->name(name: 'all');
    });
