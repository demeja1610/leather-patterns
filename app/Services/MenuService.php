<?php

declare(strict_types=1);

namespace App\Services;

use App\Dto\MenuItem\MenuItemDto;
use App\Dto\MenuItem\MenuItemListDto;
use App\Interfaces\Services\MenuServiceInterface;

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
            ),

            new MenuItemDto(
                text: __(key: 'admin_menu.pattern_tag.pattern_tags'),
                route: 'admin.page.pattern-tag.list',
                icon: 'tag',
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
                        text: __(key: 'admin_menu.pattern_author_social.pattern_author_socials'),
                        route: 'admin.page.pattern-author-social.list',
                        icon: 'external-link',
                    ),
                ),
            ),

            new MenuItemDto(
                text: __(key: 'admin_menu.pattern.patterns'),
                route: 'admin.page.patterns.list',
                icon: 'pattern',
                subMenu: new MenuItemListDto(
                    new MenuItemDto(
                        text: __(key: 'admin_menu.pattern.patterns'),
                        route: 'admin.page.patterns.list',
                        icon: 'pattern',
                    ),

                    new MenuItemDto(
                        text: __(key: 'admin_menu.pattern_review.pattern_reviews'),
                        route: 'admin.page.pattern-review.list',
                        icon: 'star',
                    ),
                ),
            ),
        );
    }
}
