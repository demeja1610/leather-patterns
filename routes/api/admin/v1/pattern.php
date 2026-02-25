<?php

declare(strict_types=1);

use Illuminate\Support\Facades\Route;

Route::prefix('pattern')
    ->name('pattern.')
    ->group(callback: function (): void {
        Route::get('search', \App\Http\Controllers\Admin\Pattern\Api\v1\SearchController::class)
            ->name(name: 'search');
    });
