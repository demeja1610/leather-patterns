<?php

declare(strict_types=1);

use Illuminate\Support\Facades\Route;

Route::prefix('admin')
    ->name('admin.')
    ->middleware([
        'auth',
        // 'throttle:60,1',
        // EnsureUserCanAccessAdminPanel::class,
    ])
    ->group(callback: function (): void {
        Route::get('/', \App\Http\Controllers\Admin\IndexController::class)
            ->name(name: 'page.index.dashboard');

        require_once __DIR__ . '/admin/pattern-category.php';

        require_once __DIR__ . '/admin/pattern-tag.php';

        require_once __DIR__ . '/admin/pattern-author.php';

        require_once __DIR__ . '/admin/pattern-authors-social.php';

        require_once __DIR__ . '/admin/pattern-review.php';

        require_once __DIR__ . '/admin/pattern.php';

        require_once __DIR__ . '/admin/pattern-file.php';
    });
