<?php

declare(strict_types=1);

namespace App\ViewComposers\Admin;

use App\Dto\MenuItem\MenuItemDto;
use App\Dto\MenuItem\MenuItemListDto;
use Illuminate\View\View;
use App\Interfaces\Services\Menu\MenuServiceInterface;
use Illuminate\Http\Request;

class SidebarMenuComposer
{
    public function __construct(
        protected readonly MenuServiceInterface $menuService,
    ) {}

    public function compose(View $view): void
    {
        $request = request();
        $menu = $this->getMenu(request: $request);

        $view->with(key: [
            'menu' => $menu,
        ]);
    }

    protected function getMenu(Request &$request): MenuItemListDto
    {
        $menu = $this->menuService->getAdminMenu();

        $menuWithActiveItems = $this->setActiveMenuItemsIfExists(
            menu: $menu,
            request: $request,
        );

        unset($menu);

        return $menuWithActiveItems;
    }

    protected function setActiveMenuItemsIfExists(MenuItemListDto &$menu, Request &$request): MenuItemListDto
    {
        $currentRouteName = $request->route()->getName();

        return new MenuItemListDto(
            ...array_map(
                callback: function (MenuItemDto $menuItem) use (&$currentRouteName, &$request): MenuItemDto {
                    if (
                        $menuItem->getRoute() !== $currentRouteName && !$menuItem->getSubMenu() instanceof MenuItemListDto
                    ) {
                        return $menuItem;
                    }

                    $subMenu = $menuItem->getSubMenu();
                    $hasActiveSubMenuItem = false;

                    if (
                        $subMenu instanceof MenuItemListDto
                        && !$subMenu->isEmpty()
                    ) {
                        $subMenu = $this->setActiveMenuItemsIfExists(
                            menu: $subMenu,
                            request: $request,
                        );

                        /**
                         * @var \App\Dto\MenuItem\MenuItemDto $subMenuItem
                         */
                        foreach ($subMenu as $subMenuItem) {
                            if ($subMenuItem->isActive() === true) {
                                $hasActiveSubMenuItem = true;

                                break;
                            }
                        }
                    }

                    $currentRouteContainsMenuItemRoutePart = false;
                    $itemRoutePart = implode(
                        separator: '.',
                        array: array_slice(
                            array: explode(
                                separator: '.',
                                string: (string) $currentRouteName,
                            ),
                            offset: 0,
                            length: -1,
                        ),
                    );

                    if ($menuItem->getRoute() !== null && str_contains(haystack: $menuItem->getRoute(), needle: $itemRoutePart)) {
                        $currentRouteContainsMenuItemRoutePart = true;
                    }

                    return new MenuItemDto(
                        text: $menuItem->getText(),
                        route: $menuItem->getRoute(),
                        icon: $menuItem->getIcon(),
                        subMenu: $subMenu,
                        isActive: $menuItem->getRoute() === $currentRouteName || $hasActiveSubMenuItem || $currentRouteContainsMenuItemRoutePart,
                    );
                },
                array: $menu->getItems(),
            ),
        );
    }
}
