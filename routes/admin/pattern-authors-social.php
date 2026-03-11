<?php

declare(strict_types=1);

use Illuminate\Support\Facades\Route;

Route::prefix('authors-socials')
    ->group(callback: function (): void {
        Route::get('/', \App\Http\Controllers\Admin\PatternAuthorSocial\Page\ListPageController::class)
            ->name(name: 'page.pattern-author-socials.list');

        Route::group(['prefix' => 'create'], function (): void {
            Route::get('/', \App\Http\Controllers\Admin\PatternAuthorSocial\Page\CreatePageController::class)
                ->name(name: 'page.pattern-author-socials.create');

            Route::post('/', \App\Http\Controllers\Admin\PatternAuthorSocial\Action\CreateController::class)
                ->name(name: 'pattern-author-socials.create');
        });

        Route::group(['prefix' => '{id}'], function (): void {
            Route::prefix('edit')
                ->group(callback: function (): void {
                    Route::get('/', \App\Http\Controllers\Admin\PatternAuthorSocial\Page\EditPageController::class)
                        ->name(name: 'page.pattern-author-socials.edit');

                    Route::patch('/', \App\Http\Controllers\Admin\PatternAuthorSocial\Action\EditController::class)
                        ->name(name: 'pattern-author-socials.update');
                });

            Route::get('/delete', \App\Http\Controllers\Admin\PatternAuthorSocial\Action\DeleteController::class)
                ->name(name: 'pattern-author-socials.delete');
        });
    });
