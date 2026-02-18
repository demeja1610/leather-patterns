<?php

use Illuminate\Support\Facades\Route;

Route::prefix('categories')
    ->group(function (): void {
        Route::get('/', \App\Http\Controllers\Admin\PatternCategory\Page\ListPageController::class)
            ->name('page.pattern-category.list');

        Route::group(['prefix' => 'create'], function (): void {
            Route::get('/', \App\Http\Controllers\Admin\PatternCategory\Page\CreatePageController::class)
                ->name('page.pattern-category.create');

            Route::post('/', \App\Http\Controllers\Admin\PatternCategory\Action\CreateController::class)
                ->name('pattern-category.create');
        });

        Route::group(['prefix' => '{id}'], function (): void {
            Route::prefix('edit')
                ->group(function () {
                    Route::get('/', \App\Http\Controllers\Admin\PatternCategory\Page\EditPageController::class)
                        ->name('page.pattern-category.edit');

                    Route::patch('/', \App\Http\Controllers\Admin\PatternCategory\Action\EditController::class)
                        ->name('pattern-category.update');
                });

            Route::get('/delete', \App\Http\Controllers\Admin\PatternCategory\Action\DeleteController::class)
                ->name('pattern-category.delete');
        });
    });
