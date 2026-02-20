<?php

use Illuminate\Support\Facades\Route;

Route::middleware('throttle:60,1')->group(function (): void {
    require_once __DIR__ . '/auth.php';

    Route::get('/', \App\Http\Controllers\Pattern\Web\v1\ListController::class)
        ->name('page.index');

    Route::get('/pattern/{id}', \App\Http\Controllers\Pattern\Web\v1\SingleController::class)
        ->name('page.pattern.single');
});

require_once __DIR__ . '/admin.php';
