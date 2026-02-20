<?php

use Illuminate\Support\Facades\Route;

Route::middleware('throttle:60,1')
    ->name('api.')
    ->group(callback: function (): void {
        require_once __DIR__ . '/api/v1/v1.php';
    });
