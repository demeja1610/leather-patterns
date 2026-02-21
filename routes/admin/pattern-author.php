<?php

declare(strict_types=1);

use Illuminate\Support\Facades\Route;

Route::prefix('authors')
    ->group(callback: function (): void {
        Route::get('/', \App\Http\Controllers\Admin\PatternAuthor\Page\ListPageController::class)
            ->name(name: 'page.pattern-author.list');

        Route::group(['prefix' => 'create'], function (): void {
            Route::get('/', \App\Http\Controllers\Admin\PatternAuthor\Page\CreatePageController::class)
                ->name(name: 'page.pattern-author.create');

            Route::post('/', \App\Http\Controllers\Admin\PatternAuthor\Action\CreateController::class)
                ->name(name: 'pattern-author.create');
        });

        Route::group(['prefix' => '{id}'], function (): void {
            Route::prefix('edit')
                ->group(callback: function (): void {
                    Route::get('/', \App\Http\Controllers\Admin\PatternAuthor\Page\EditPageController::class)
                        ->name(name: 'page.pattern-author.edit');

                    Route::patch('/', \App\Http\Controllers\Admin\PatternAuthor\Action\EditController::class)
                        ->name(name: 'pattern-author.update');
                });

            Route::get('/delete', \App\Http\Controllers\Admin\PatternAuthor\Action\DeleteController::class)
                ->name(name: 'pattern-author.delete');
        });
    });
