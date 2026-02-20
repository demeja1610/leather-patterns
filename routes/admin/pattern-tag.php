<?php

use Illuminate\Support\Facades\Route;

Route::prefix('tags')
    ->group(function (): void {
        Route::get('/', \App\Http\Controllers\Admin\PatternTag\Page\ListPageController::class)
            ->name('page.pattern-tag.list');

        Route::group(['prefix' => 'create'], function (): void {
            Route::get('/', \App\Http\Controllers\Admin\PatternTag\Page\CreatePageController::class)
                ->name('page.pattern-tag.create');

            Route::post('/', \App\Http\Controllers\Admin\PatternTag\Action\CreateController::class)
                ->name('pattern-tag.create');
        });

        Route::group(['prefix' => '{id}'], function (): void {
            Route::prefix('edit')
                ->group(function (): void {
                    Route::get('/', \App\Http\Controllers\Admin\PatternTag\Page\EditPageController::class)
                        ->name('page.pattern-tag.edit');

                    Route::patch('/', \App\Http\Controllers\Admin\PatternTag\Action\EditController::class)
                        ->name('pattern-tag.update');
                });

            Route::get('/delete', \App\Http\Controllers\Admin\PatternTag\Action\DeleteController::class)
                ->name('pattern-tag.delete');
        });
    });
