<?php

declare(strict_types=1);

use Illuminate\Support\Facades\Route;

Route::prefix('pattern-tag')
    ->name('pattern-tag.')
    ->group(callback: function (): void {
        Route::get('search-replace', \App\Http\Controllers\Admin\PatternTag\Api\v1\SearchReplaceController::class)
            ->name(name: 'search-replace');
    });
