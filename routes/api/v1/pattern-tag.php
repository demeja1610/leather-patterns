<?php

declare(strict_types=1);

use Illuminate\Support\Facades\Route;

Route::prefix('pattern-tags')
    ->name('pattern-tags.')
    ->group(callback: function (): void {
        Route::get('all', \App\Http\Controllers\PatternTag\Api\v1\GetAllController::class)
            ->name(name: 'all');
    });
