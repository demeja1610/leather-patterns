<?php

use Illuminate\Support\Facades\Route;

Route::prefix('pattern-category')
    ->name('pattern-category.')
    ->group(function () {
        Route::get('all', \App\Http\Controllers\PatternCategory\Api\v1\GetAllController::class)
            ->name('all');
    });
