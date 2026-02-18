<?php

declare(strict_types=1);

use App\Enum\PatternOrderEnum;

return [
    'filter_categories_title' => 'Категории',
    'filter_categories_search' => 'Поиск',

    'filter_tags_title' => 'Теги',
    'filter_tags_search' => 'Поиск',

    'filter_authors_title' => 'Авторы',
    'filter_authors_search' => 'Поиск',

    'filter_with_video_title' => 'С видео',
    'filter_with_reviews_title' => 'С отзывами',
    'filter_with_author_title' => 'С автором',

    'other_filters_title' => 'Другое',

    'apply' => 'Применить фильтры',
    'reset' => 'Убрать фильтры',
    'search_placeholder' => 'Поиск',
    'sort' => 'Сортировка',
    'show_all' => 'Показать все',
    'hide' => 'Скрыть',
    'not_selected' => 'Не выбрано',

    'filters' => 'Фильтры',
    'id' => 'ID',
    'name' => 'Название',
    'older_than' => 'Старее чем',
    'newer_than' => 'Новее чем',

    'pattern_order' => [
        'default' => 'По умолчанию',
        PatternOrderEnum::DATE_ASC->value => 'По дате (от меньшего к большему)',
        PatternOrderEnum::DATE_DESC->value => 'По дате (от большего к меньшему)',
        PatternOrderEnum::RATING_DESC->value => 'По рейтингу (от большего к меньшему)',
    ],
];
