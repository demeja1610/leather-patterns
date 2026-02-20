<?php

use Illuminate\Support\Facades\Route;

Route::prefix('v1')
    ->name('v1.')
    ->group(function (): void {
        require_once __DIR__ . '/pattern-category.php';

        require_once __DIR__ . '/pattern-tag.php';

        require_once __DIR__ . '/pattern-author.php';
    });
