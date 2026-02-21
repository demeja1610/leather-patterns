<?php

declare(strict_types=1);

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
                text: __(key: 'admin_menu.index_page'),
                route: 'admin.page.index.dashboard',
                icon: 'home',
            ),
            new MenuItemDto(
                text: __(key: 'admin_menu.pattern_category.pattern_categories'),
                route: 'admin.page.pattern-category.list',
                icon: 'category',
                subMenu: new MenuItemListDto(
                    new MenuItemDto(
                        text: __(key: 'admin_menu.pattern_category.pattern_categories'),
                        route: 'admin.page.pattern-category.list',
                        icon: 'category',
                    ),
                    new MenuItemDto(
                        text: __(key: 'admin_menu.pattern_category.add_new'),
                        route: 'admin.page.pattern-category.create',
                        icon: 'create',
                    ),
                ),
            ),
            new MenuItemDto(
                text: __(key: 'admin_menu.pattern_tag.pattern_tags'),
                route: 'admin.page.pattern-tag.list',
                icon: 'tag',
                subMenu: new MenuItemListDto(
                    new MenuItemDto(
                        text: __(key: 'admin_menu.pattern_tag.pattern_tags'),
                        route: 'admin.page.pattern-tag.list',
                        icon: 'tag',
                    ),
                    new MenuItemDto(
                        text: __(key: 'admin_menu.pattern_tag.add_new'),
                        route: 'admin.page.pattern-tag.create',
                        icon: 'create',
                    ),
                ),
            ),
            new MenuItemDto(
                text: __(key: 'admin_menu.pattern_author.pattern_authors'),
                route: 'admin.page.pattern-author.list',
                icon: 'author',
                subMenu: new MenuItemListDto(
                    new MenuItemDto(
                        text: __(key: 'admin_menu.pattern_author.pattern_authors'),
                        route: 'admin.page.pattern-author.list',
                        icon: 'author',
                    ),
                    new MenuItemDto(
                        text: __(key: 'admin_menu.pattern_author.add_new'),
                        route: 'admin.page.pattern-author.create',
                        icon: 'create',
                    ),
                ),
            ),
        );
    }
}
