<?php

use Illuminate\Support\Facades\Route;

Route::prefix('pattern-tag')
    ->name('pattern-tag.')
    ->group(callback: function (): void {
        Route::get('all', \App\Http\Controllers\PatternTag\Api\v1\GetAllController::class)
            ->name(name: 'all');
    });
