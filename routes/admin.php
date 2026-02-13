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
    ->group(function (): void {
        Route::get('/', \App\Http\Controllers\Admin\IndexController::class)
            ->name('page.index.dashboard');

        require_once __DIR__ . '/admin/pattern-category.php';

        // Route::prefix('authors')
        //     ->group(function (): void {
        //         Route::get('/', \App\Http\Controllers\App\Admin\Author\ListPageController::class)
        //             ->name('page.admin.author.list');

        //         Route::group(['prefix' => 'create'], function (): void {
        //             Route::get('/', \App\Http\Controllers\App\Admin\Author\CreatePageController::class)
        //                 ->name('page.admin.author.create');

        //             Route::post('/', \App\Http\Controllers\App\Admin\Author\CreateController::class)
        //                 ->name('admin.author.create');
        //         });

        //         Route::post('/mass-action', \App\Http\Controllers\App\Admin\Author\MassActionController::class)
        //             ->name('admin.author.mass-action');

        //         Route::get('/search', \App\Http\Controllers\App\Admin\Author\SearchController::class)
        //             ->name('admin.author.search');

        //         Route::group(['prefix' => '{id}'], function (): void {
        //             Route::get('/', \App\Http\Controllers\App\Admin\Author\EditPageController::class)
        //                 ->name('page.admin.author.edit');

        //             Route::patch('/', \App\Http\Controllers\App\Admin\Author\EditController::class)
        //                 ->name('admin.author.update');

        //             Route::delete('/', \App\Http\Controllers\App\Admin\Author\DeleteController::class)
        //                 ->name('admin.author.delete');
        //         });
        //     });

        // Route::prefix('tags')
        //     ->group(function (): void {
        //         Route::get('/', \App\Http\Controllers\App\Admin\Tag\ListPageController::class)
        //             ->name('page.admin.tag.list');

        //         Route::group(['prefix' => 'create'], function (): void {
        //             Route::get('/', \App\Http\Controllers\App\Admin\Tag\CreatePageController::class)
        //                 ->name('page.admin.tag.create');

        //             Route::post('/', \App\Http\Controllers\App\Admin\Tag\CreateController::class)
        //                 ->name('admin.tag.create');
        //         });

        //         Route::post('/mass-action', \App\Http\Controllers\App\Admin\Tag\MassActionController::class)
        //             ->name('admin.tag.mass-action');

        //         Route::get('/search', \App\Http\Controllers\App\Admin\Tag\SearchController::class)
        //             ->name('admin.tag.search');

        //         Route::group(['prefix' => '{id}'], function (): void {
        //             Route::get('/', \App\Http\Controllers\App\Admin\Tag\EditPageController::class)
        //                 ->name('page.admin.tag.edit');

        //             Route::patch('/', \App\Http\Controllers\App\Admin\Tag\EditController::class)
        //                 ->name('admin.tag.update');

        //             Route::delete('/', \App\Http\Controllers\App\Admin\Tag\DeleteController::class)
        //                 ->name('admin.tag.delete');
        //         });
        //     });

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
