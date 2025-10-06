<?php

use Illuminate\Support\Facades\Route;

Route::get('/', \App\Http\Controllers\PatternsListController::class)
    ->name('page.index');

Route::get('/pattern/{patternId}', \App\Http\Controllers\PatternSingleController::class)
    ->name('page.pattern.single');
