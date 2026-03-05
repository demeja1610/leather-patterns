<?php

declare(strict_types=1);

use Illuminate\Http\Request;
use Illuminate\Foundation\Application;
use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;

return Application::configure(basePath: dirname(path: __DIR__))
    ->withRouting(
        web: __DIR__ . '/../routes/web.php',
        api: __DIR__ . '/../routes/api.php',
        commands: __DIR__ . '/../routes/console.php',
        health: '/up',
        apiPrefix: 'api',
    )
    ->withMiddleware(callback: function (Middleware $middleware): void {
        $middleware->redirectGuestsTo(
            redirect: fn(Request $request): string => route(name: 'page.auth.login'),
        );

        $middleware->statefulApi();
    })
    ->withSchedule(callback: function (Schedule $schedule): void {
        $schedule->job(new \App\Jobs\PatternImage\ClearTempPatternImagesDirectoryJob())
            ->dailyAt('23:59:59');

        $schedule->job(new \App\Jobs\PatternFile\ClearTempPatternFilesDirectoryJob())
            ->dailyAt('23:59:59');

        $schedule->job(new \App\Jobs\Pattern\RemoveFromPatternsMarkedForRemovalPatternAuthorsJob)
            ->dailyAt('23:59:59');

        $schedule->job(new \App\Jobs\Pattern\RemoveFromPatternsMarkedForRemovalPatternCategoriesJob)
            ->dailyAt('23:59:59');

        $schedule->job(new \App\Jobs\Pattern\RemoveFromPatternsMarkedForRemovalPatternTagsJob)
            ->dailyAt('23:59:59');

        $schedule->job(new \App\Jobs\Pattern\ReplaceMarkedForReplacePatternAuthorsInPatternsJob)
            ->dailyAt('23:59:59');

        $schedule->job(new \App\Jobs\Pattern\ReplaceMarkedForReplacePatternCategoriesInPatternsJob)
            ->dailyAt('23:59:59');

        $schedule->job(new \App\Jobs\Pattern\ReplaceMarkedForReplacePatternTagsInPatternsJob)
            ->dailyAt('23:59:59');
    })
    ->withExceptions(using: function (Exceptions $exceptions): void {})->create();
