<?php

use Illuminate\Support\Facades\Route;

Route::prefix('pattern-author')
    ->name('pattern-author.')
    ->group(function (): void {
        Route::get('all', \App\Http\Controllers\PatternAuthor\Api\v1\GetAllController::class)
            ->name('all');
    });
