<?php

declare(strict_types=1);

use Illuminate\Support\Facades\Route;

Route::prefix('videos')
    ->group(callback: function (): void {
        Route::get('/', \App\Http\Controllers\Admin\PatternVideo\Page\ListPageController::class)
            ->name(name: 'page.pattern-videos.list');

        Route::group(['prefix' => 'create'], function (): void {
            Route::get('/', \App\Http\Controllers\Admin\PatternVideo\Page\CreatePageController::class)
                ->name(name: 'page.pattern-videos.create');

            Route::post('/', \App\Http\Controllers\Admin\PatternVideo\Action\CreateController::class)
                ->name(name: 'pattern-videos.create');
        });

        Route::group(['prefix' => '{id}'], function (): void {
            Route::prefix('edit')
                ->group(callback: function (): void {
                    Route::get('/', \App\Http\Controllers\Admin\PatternVideo\Page\EditPageController::class)
                        ->name(name: 'page.pattern-videos.edit');

                    Route::patch('/', \App\Http\Controllers\Admin\PatternVideo\Action\EditController::class)
                        ->name(name: 'pattern-videos.update');
                });

            Route::get('/delete', \App\Http\Controllers\Admin\PatternVideo\Action\DeleteController::class)
                ->name(name: 'pattern-videos.delete');
        });
    });
