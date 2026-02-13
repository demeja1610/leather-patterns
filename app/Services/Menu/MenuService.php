<?php

namespace App\Services\Menu;

use App\Dto\MenuItem\MenuItemDto;
use App\Dto\MenuItem\MenuItemListDto;
use App\Interfaces\Services\Menu\MenuServiceInterface;

class MenuService implements MenuServiceInterface
{
    public function getAdminMenu(): MenuItemListDto
    {
        return new MenuItemListDto(
            new MenuItemDto(
                text: __('admin_menu.index_page'),
                route: 'admin.page.index.dashboard',
                icon: 'home',
            ),
            new MenuItemDto(
                text: __('admin_menu.pattern_category.pattern_categories'),
                route: 'admin.page.pattern-category.list',
                icon: 'list',
                subMenu: new MenuItemListDto(
                    new MenuItemDto(
                        text: __('admin_menu.pattern_category.pattern_categories'),
                        route: 'admin.page.pattern-category.list',
                        icon: 'list',
                    ),
                ),
            ),
        );
    }
}
