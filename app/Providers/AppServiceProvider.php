<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void {
        $this->registerInterfaces();
    }

    public function boot(): void
    {
        $this->registerObservers();
    }

    protected function registerObservers(): void
    {
        \App\Models\Pattern::observe(\App\Observers\PatternObserver::class);
    }

    protected function registerInterfaces(): void
    {
        $this->app->bind(
            abstract: \App\Interfaces\Services\ParserServiceInterface::class,
            concrete: \App\Services\ParserService::class
        );
    }
}
