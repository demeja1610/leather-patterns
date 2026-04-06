<?php

declare(strict_types=1);

use Illuminate\Support\Facades\Route;

Route::prefix('patterns')
    ->group(callback: function (): void {
        Route::get('/', \App\Http\Controllers\Admin\Pattern\Page\ListPageController::class)
            ->name(name: 'page.patterns.list');

        Route::group(['prefix' => 'create'], function (): void {
            Route::get('/', \App\Http\Controllers\Admin\Pattern\Page\CreatePageController::class)
                ->name(name: 'page.patterns.create');

            Route::post('/', \App\Http\Controllers\Admin\Pattern\Action\CreateController::class)
                ->name(name: 'patterns.create');
        });

        Route::group(['prefix' => '{id}'], function (): void {
            Route::prefix('edit')
                ->group(callback: function (): void {
                    Route::get('/', \App\Http\Controllers\Admin\Pattern\Page\EditPageController::class)
                        ->name(name: 'page.patterns.edit');

                    Route::patch('/', \App\Http\Controllers\Admin\Pattern\Action\EditController::class)
                        ->name(name: 'patterns.update');
                });

            Route::get('/delete', \App\Http\Controllers\Admin\Pattern\Action\DeleteController::class)
                ->name(name: 'pattern.delete');
        });
    });
