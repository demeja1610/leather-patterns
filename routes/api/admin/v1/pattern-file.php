<?php

declare(strict_types=1);

use Illuminate\Support\Facades\Route;

Route::prefix('pattern-file')
    ->name('pattern-file.')
    ->group(callback: function (): void {
        Route::post('upload', \App\Http\Controllers\Admin\PatternFile\Action\UploadController::class)
            ->name(name: 'upload');
    });
