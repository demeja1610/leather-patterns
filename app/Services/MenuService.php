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
                text: __('admin_menu.index_page'),
                route: 'admin.page.index.dashboard',
                icon: 'home',
            ),

            new MenuItemDto(
                text: __('admin_menu.pattern_category.pattern_categories'),
                route: 'admin.page.pattern-categories.list',
                icon: 'category',
                subMenu: new MenuItemListDto(
                    new MenuItemDto(
                        text: __('admin_menu.pattern_category.list'),
                        route: 'admin.page.pattern-categories.list',
                        icon: 'category',
                    ),
                    new MenuItemDto(
                        text: __('admin_menu.pattern_category.add_new'),
                        route: 'admin.page.pattern-categories.create',
                        icon: 'create',
                    ),
                )
            ),

            new MenuItemDto(
                text: __('admin_menu.pattern_tag.pattern_tags'),
                route: 'admin.page.pattern-tags.list',
                icon: 'tag',
                subMenu: new MenuItemListDto(
                    new MenuItemDto(
                        text: __('admin_menu.pattern_tag.list'),
                        route: 'admin.page.pattern-tags.list',
                        icon: 'tag',
                    ),
                    new MenuItemDto(
                        text: __('admin_menu.pattern_tag.add_new'),
                        route: 'admin.page.pattern-tags.create',
                        icon: 'create',
                    ),
                )
            ),

            new MenuItemDto(
                text: __('admin_menu.pattern_author.pattern_authors'),
                route: 'admin.page.pattern-authors.list',
                icon: 'author',
                subMenu: new MenuItemListDto(
                    new MenuItemDto(
                        text: __('admin_menu.pattern_author.list'),
                        route: 'admin.page.pattern-authors.list',
                        icon: 'author',
                    ),
                    new MenuItemDto(
                        text: __('admin_menu.pattern_author.add_new'),
                        route: 'admin.page.pattern-authors.create',
                        icon: 'create',
                    ),

                    new MenuItemDto(
                        text: __('admin_menu.pattern_author_social.pattern_author_socials'),
                        route: 'admin.page.pattern-author-socials.list',
                        icon: 'external-link',
                        subMenu: new MenuItemListDto(
                            new MenuItemDto(
                                text: __('admin_menu.pattern_author_social.list'),
                                route: 'admin.page.pattern-author-socials.list',
                                icon: 'external-link',
                            ),
                            new MenuItemDto(
                                text: __('admin_menu.pattern_author_social.add_new'),
                                route: 'admin.page.pattern-author-socials.create',
                                icon: 'create',
                            ),
                        ),
                    ),
                ),
            ),

            new MenuItemDto(
                text: __('admin_menu.pattern.patterns'),
                route: 'admin.page.patterns.list',
                icon: 'pattern',
                subMenu: new MenuItemListDto(
                    new MenuItemDto(
                        text: __('admin_menu.pattern.list'),
                        route: 'admin.page.patterns.list',
                        icon: 'pattern',
                    ),
                    new MenuItemDto(
                        text: __('admin_menu.pattern.add_new'),
                        route: 'admin.page.patterns.create',
                        icon: 'create',
                    ),
                ),
            ),

            new MenuItemDto(
                text: __('admin_menu.pattern_file.files'),
                route: 'admin.page.pattern-files.list',
                icon: 'file',
                subMenu: new MenuItemListDto(
                    new MenuItemDto(
                        text: __('admin_menu.pattern_file.list'),
                        route: 'admin.page.pattern-files.list',
                        icon: 'file',
                    ),
                    new MenuItemDto(
                        text: __('admin_menu.pattern_file.duplicates'),
                        route: 'admin.page.pattern-files.duplicates',
                        icon: 'copy',
                    ),
                )
            ),

            new MenuItemDto(
                text: __('admin_menu.pattern_review.pattern_reviews'),
                route: 'admin.page.pattern-review.list',
                icon: 'star',
                subMenu: new MenuItemListDto(
                    new MenuItemDto(
                        text: __('admin_menu.pattern_review.list'),
                        route: 'admin.page.pattern-review.list',
                        icon: 'star',
                    ),
                )
            ),
        );
    }
}
