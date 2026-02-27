<?php

declare(strict_types=1);

use Illuminate\Support\Facades\Route;

Route::prefix('pattern-tag')
    ->name('pattern-tag.')
    ->group(callback: function (): void {
        Route::get('search', \App\Http\Controllers\Admin\PatternTag\Api\v1\SearchController::class)
            ->name(name: 'search');
    });
