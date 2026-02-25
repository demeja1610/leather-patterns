<?php

declare(strict_types=1);

use Illuminate\Support\Facades\Route;

Route::prefix('pattern-category')
    ->name('pattern-category.')
    ->group(callback: function (): void {
        Route::get('search', \App\Http\Controllers\Admin\PatternCategory\Api\v1\SearchController::class)
            ->name(name: 'search');

        Route::get('search-replace', \App\Http\Controllers\Admin\PatternCategory\Api\v1\SearchReplaceController::class)
            ->name(name: 'search-replace');
    });
