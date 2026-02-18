<?php

declare(strict_types=1);

use App\Enum\PatternOrderEnum;

return [
    'filter_categories_title' => 'Categories',
    'filter_categories_search' => 'Categories search',

    'filter_tags_title' => 'Tags',
    'filter_tags_search' => 'Tags search',

    'filter_authors_title' => 'Authors',
    'filter_authors_search' => 'Authors search',

    'filter_with_video_title' => 'With video',
    'filter_with_reviews_title' => 'With reviews',
    'filter_with_author_title' => 'With author',

    'other_filters_title' => 'Other',

    'apply' => 'Apply filters',
    'reset' => 'Reset filters',
    'search_placeholder' => 'Search',
    'sort' => 'Sort',
    'show_all' => 'Show all',
    'hide' => 'Hide',
    'not_selected' => 'Not selected',

    'filters' => 'Filters',
    'id' => 'ID',
    'name' => 'Name',
    'older_than' => 'Older than',
    'newer_than' => 'Newer than',

    'pattern_order' => [
        'default' => 'Default',
        PatternOrderEnum::DATE_ASC->value => 'By date (ascending)',
        PatternOrderEnum::DATE_DESC->value => 'By date (descending)',
        PatternOrderEnum::RATING_DESC->value => 'By rating (descending)',
    ],
];
