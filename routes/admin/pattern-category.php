<?php

declare(strict_types=1);

use Illuminate\Support\Facades\Route;

Route::prefix('categories')
    ->group(callback: function (): void {
        Route::get('/', \App\Http\Controllers\Admin\PatternCategory\Page\ListPageController::class)
            ->name(name: 'page.pattern-category.list');

        Route::group(['prefix' => 'create'], function (): void {
            Route::get('/', \App\Http\Controllers\Admin\PatternCategory\Page\CreatePageController::class)
                ->name(name: 'page.pattern-category.create');

            Route::post('/', \App\Http\Controllers\Admin\PatternCategory\Action\CreateController::class)
                ->name(name: 'pattern-category.create');
        });

        Route::group(['prefix' => '{id}'], function (): void {
            Route::prefix('edit')
                ->group(callback: function (): void {
                    Route::get('/', \App\Http\Controllers\Admin\PatternCategory\Page\EditPageController::class)
                        ->name(name: 'page.pattern-category.edit');

                    Route::patch('/', \App\Http\Controllers\Admin\PatternCategory\Action\EditController::class)
                        ->name(name: 'pattern-category.update');
                });

            Route::get('/delete', \App\Http\Controllers\Admin\PatternCategory\Action\DeleteController::class)
                ->name(name: 'pattern-category.delete');
        });
    });
