<?php

declare(strict_types=1);

use Illuminate\Support\Facades\Route;

Route::prefix('admin')
    ->name('admin.')
    ->middleware([
        'auth',
        'throttle:60,1',
        // EnsureUserCanAccessAdminPanel::class,
    ])
    ->group(callback: function (): void {
        Route::get('/', \App\Http\Controllers\Admin\IndexController::class)
            ->name(name: 'page.index.dashboard');

        require_once __DIR__ . '/admin/pattern-category.php';

        require_once __DIR__ . '/admin/pattern-tag.php';

        require_once __DIR__ . '/admin/pattern-author.php';

        require_once __DIR__ . '/admin/pattern-review.php';

        // Route::prefix('pattern')
        //     ->group(function (): void {
        //         Route::get('/', \App\Http\Controllers\App\Admin\Pattern\ListPageController::class)
        //             ->name('page.admin.pattern.list');

        //         Route::group(['prefix' => 'create'], function (): void {
        //             Route::get('/', \App\Http\Controllers\App\Admin\Pattern\CreatePageController::class)
        //                 ->name('page.admin.pattern.create');

        //             Route::post('/', \App\Http\Controllers\App\Admin\Pattern\CreateController::class)
        //                 ->name('admin.pattern.create');
        //         });

        //         Route::group(['prefix' => '{id}'], function (): void {
        //             Route::get('/', \App\Http\Controllers\App\Admin\Pattern\EditPageController::class)
        //                 ->name('page.admin.pattern.edit');

        //             Route::patch('/', \App\Http\Controllers\App\Admin\Pattern\EditController::class)
        //                 ->name('admin.pattern.update');

        //             // Route::delete('/', \App\Http\Controllers\App\Admin\Pattern\DeleteController::class)
        //             //     ->name('admin.pattern.delete');
        //         });

        //         Route::get('/search', \App\Http\Controllers\App\Admin\Pattern\SearchController::class)
        //             ->name('admin.pattern.search');
        //     });
    });
