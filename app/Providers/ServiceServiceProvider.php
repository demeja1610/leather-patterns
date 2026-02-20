<?php

declare(strict_types=1);

namespace App\Providers;

use Illuminate\Support\ServiceProvider;

class ServiceServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->bind(
            abstract: \App\Interfaces\Services\ParserServiceInterface::class,
            concrete: \App\Services\ParserService::class
        );

        $this->app->bind(
            abstract: \App\Interfaces\Services\Menu\MenuServiceInterface::class,
            concrete: \App\Services\Menu\MenuService::class
        );
    }

    public function boot(): void {}
}
