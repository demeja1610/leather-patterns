<?php

namespace App\Providers;

use Illuminate\Support\Facades\View;
use Illuminate\Support\ServiceProvider;

class ViewComposerServiceProvider extends ServiceProvider
{
    public function register(): void {}

    public function boot(): void
    {
        View::composer('components.admin.sidebar.sidebar', \App\ViewComposers\Admin\SidebarMenuComposer::class);
    }
}
