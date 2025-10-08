<?php

declare(strict_types=1);

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use MoonShine\Contracts\Core\DependencyInjection\ConfiguratorContract;
use MoonShine\Contracts\Core\DependencyInjection\CoreContract;
use MoonShine\Laravel\DependencyInjection\MoonShine;
use MoonShine\Laravel\DependencyInjection\MoonShineConfigurator;
use App\MoonShine\Resources\MoonShineUserResource;
use App\MoonShine\Resources\MoonShineUserRoleResource;
use App\MoonShine\Resources\User\UserResource;
use App\MoonShine\Resources\PatternCategory\PatternCategoryResource;
use App\MoonShine\Resources\PatternTag\PatternTagResource;
use App\MoonShine\Resources\PatternAuthor\PatternAuthorResource;

class MoonShineServiceProvider extends ServiceProvider
{
    /**
     * @param  MoonShine  $core
     * @param  MoonShineConfigurator  $config
     *
     */
    public function boot(CoreContract $core, ConfiguratorContract $config): void
    {
        $core
            ->resources([
                MoonShineUserResource::class,
                MoonShineUserRoleResource::class,
                UserResource::class,
                PatternCategoryResource::class,
                PatternTagResource::class,
                PatternAuthorResource::class,
            ])
            ->pages([
                ...$config->getPages(),
            ])
        ;
    }
}
