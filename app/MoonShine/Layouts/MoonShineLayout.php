<?php

declare(strict_types=1);

namespace App\MoonShine\Layouts;

use MoonShine\MenuManager\MenuItem;
use MoonShine\MenuManager\MenuGroup;
use MoonShine\MenuManager\MenuDivider;
use MoonShine\Laravel\Layouts\AppLayout;
use MoonShine\UI\Components\Layout\Footer;
use MoonShine\UI\Components\Layout\Layout;
use MoonShine\UI\Components\Layout\Favicon;
use App\MoonShine\Resources\User\UserResource;
use App\MoonShine\Resources\MoonShineUserRoleResource;
use MoonShine\Contracts\ColorManager\ColorManagerContract;
use App\MoonShine\Resources\PatternCategory\PatternCategoryResource;
use App\MoonShine\Resources\PatternTag\PatternTagResource;
use App\MoonShine\Resources\PatternAuthor\PatternAuthorResource;

final class MoonShineLayout extends AppLayout
{
    protected function assets(): array
    {
        return [
            ...parent::assets(),
        ];
    }

    protected function menu(): array
    {
        return [
            ...parent::menu(),
            // MenuDivider::make(),
            // MenuGroup::make('test')->setItems([
            //     MenuItem::make(__('moonshine::menu.users'), MoonShineUserRoleResource::class)
            //         ->icon('user')
            //         ->badge(fn() => 4)

            // ]),
            // MenuDivider::make('links'),
            MenuItem::make('Users', UserResource::class),

            MenuItem::make('Categories', PatternCategoryResource::class),
            MenuItem::make('Tags', PatternTagResource::class),
            MenuItem::make('Authors', PatternAuthorResource::class),
        ];
    }

    protected function colors(ColorManagerContract $colorManager): void
    {
        parent::colors($colorManager);
    }

    protected function getFooterCopyright(): string
    {
        return 'Made with ❤️ by Demeja16';
    }

    protected function getFooterComponent(): Footer
    {
        return parent::getFooterComponent()->menu([]);
    }

    protected function getFaviconComponent(): Favicon
    {
        return parent::getFaviconComponent()->customAssets([
            'apple-touch' => '/apple-touch-icon.png',
            'favicon-96x96' => '/favicon-96x96.png',
            'favicon' => '/favicon.svg',
            'web-manifest' => '/site.webmanifest'
        ]);
    }

    public function build(): Layout
    {
        return parent::build();
    }
}
