<?php

declare(strict_types=1);

use Illuminate\Support\Facades\Route;

Route::prefix('pattern-files')
    ->group(callback: function (): void {
        Route::get('/', \App\Http\Controllers\Admin\PatternFile\Page\ListPageController::class)
            ->name(name: 'page.pattern-files.list');

        Route::get('/duplicates', \App\Http\Controllers\Admin\PatternFile\Page\DuplicatesController::class)
            ->name(name: 'page.pattern-files.duplicates');

        Route::prefix('/{id}')->group(function () {
            Route::get('/delete', \App\Http\Controllers\Admin\PatternFile\Action\DeleteController::class)
                ->name('pattern-files.delete');
        });
    });
