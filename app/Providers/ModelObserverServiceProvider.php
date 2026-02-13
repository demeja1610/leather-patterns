<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;

class ModelObserverServiceProvider extends ServiceProvider
{
    public function register(): void {}

    public function boot(): void
    {
        \App\Models\Pattern::observe(\App\Observers\PatternObserver::class);
    }
}
