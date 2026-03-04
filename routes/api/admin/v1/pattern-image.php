<?php

declare(strict_types=1);

use Illuminate\Support\Facades\Route;

Route::prefix('pattern-image')
    ->name('pattern-image.')
    ->group(callback: function (): void {
        Route::post('upload', \App\Http\Controllers\Admin\PatternImage\UploadController::class)
            ->name(name: 'upload');
    });
