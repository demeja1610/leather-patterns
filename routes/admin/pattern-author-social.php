<?php

declare(strict_types=1);

use Illuminate\Support\Facades\Route;

Route::prefix('author-socials')
    ->group(callback: function (): void {
        Route::get('/', \App\Http\Controllers\Admin\PatternAuthorSocial\Page\ListPageController::class)
            ->name(name: 'page.pattern-author-social.list');

        Route::group(['prefix' => 'create'], function (): void {
            Route::get('/', \App\Http\Controllers\Admin\PatternAuthorSocial\Page\CreatePageController::class)
                ->name(name: 'page.pattern-author-social.create');

            Route::post('/', \App\Http\Controllers\Admin\PatternAuthorSocial\Action\CreateController::class)
                ->name(name: 'pattern-author-social.create');
        });

        Route::group(['prefix' => '{id}'], function (): void {
            Route::prefix('edit')
                ->group(callback: function (): void {
                    Route::get('/', \App\Http\Controllers\Admin\PatternAuthorSocial\Page\EditPageController::class)
                        ->name(name: 'page.pattern-author-social.edit');

                    Route::patch('/', \App\Http\Controllers\Admin\PatternAuthorSocial\Action\EditController::class)
                        ->name(name: 'pattern-author-social.update');
                });

            Route::get('/delete', \App\Http\Controllers\Admin\PatternAuthorSocial\Action\DeleteController::class)
                ->name(name: 'pattern-author-social.delete');
        });
    });
