<?php

use Illuminate\Support\Facades\Route;

Route::prefix('tags')
    ->group(callback: function (): void {
        Route::get('/', \App\Http\Controllers\Admin\PatternTag\Page\ListPageController::class)
            ->name(name: 'page.pattern-tag.list');

        Route::group(['prefix' => 'create'], function (): void {
            Route::get('/', \App\Http\Controllers\Admin\PatternTag\Page\CreatePageController::class)
                ->name(name: 'page.pattern-tag.create');

            Route::post('/', \App\Http\Controllers\Admin\PatternTag\Action\CreateController::class)
                ->name(name: 'pattern-tag.create');
        });

        Route::group(['prefix' => '{id}'], function (): void {
            Route::prefix('edit')
                ->group(callback: function (): void {
                    Route::get('/', \App\Http\Controllers\Admin\PatternTag\Page\EditPageController::class)
                        ->name(name: 'page.pattern-tag.edit');

                    Route::patch('/', \App\Http\Controllers\Admin\PatternTag\Action\EditController::class)
                        ->name(name: 'pattern-tag.update');
                });

            Route::get('/delete', \App\Http\Controllers\Admin\PatternTag\Action\DeleteController::class)
                ->name(name: 'pattern-tag.delete');
        });
    });
