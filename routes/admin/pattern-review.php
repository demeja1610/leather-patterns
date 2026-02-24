<?php

declare(strict_types=1);

use Illuminate\Support\Facades\Route;

Route::prefix('reviews')
    ->group(callback: function (): void {
        Route::get('/', \App\Http\Controllers\Admin\PatternReview\Page\ListPageController::class)
            ->name(name: 'page.pattern-review.list');

        Route::group(['prefix' => '{id}'], function (): void {
            Route::prefix('edit')
                ->group(callback: function (): void {
                    Route::get('/', \App\Http\Controllers\Admin\PatternReview\Page\EditPageController::class)
                        ->name(name: 'page.pattern-review.edit');

                    Route::patch('/', \App\Http\Controllers\Admin\PatternReview\Action\EditController::class)
                        ->name(name: 'pattern-review.update');
                });

            Route::get('/delete', \App\Http\Controllers\Admin\PatternReview\Action\DeleteController::class)
                ->name(name: 'pattern-review.delete');
        });
    });
