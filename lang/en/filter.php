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

    'other_filters_title' => 'Other',

    'apply' => 'Apply filters',

    'reset' => 'Reset filters',

    'search_placeholder' => 'Search',

    'sort' => 'Sort',

    'pattern_order' => [
        'default' => 'Default',
        PatternOrderEnum::DATE_ASC->value => 'By date (ascending)',
        PatternOrderEnum::DATE_DESC->value => 'By date (descending)',
        PatternOrderEnum::RATING_DESC->value => 'By rating (descending)',
    ],
];
