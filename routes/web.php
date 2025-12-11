<?php

use Illuminate\Support\Facades\Route;

require_once __DIR__ . '/auth.php';

Route::get('/', \App\Http\Controllers\PatternsListController::class)
    ->name('page.index');

Route::get('/pattern/{patternId}', \App\Http\Controllers\PatternSingleController::class)
    ->name('page.pattern.single');
