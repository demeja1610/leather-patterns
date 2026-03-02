<?php

declare(strict_types=1);

namespace App\Providers;

use Livewire\Blaze\Blaze;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void {}

    public function boot(): void
    {
        Blaze::optimize()
            ->in(resource_path('views/components'))
            ->in(resource_path('views/components/admin/sidebar'), compile: false)
            ->in(resource_path('views/components/sidebar'), compile: false)
        ;
    }
}
