<?php

declare(strict_types=1);

use App\Enum\PatternOrderEnum;
use App\Enum\OrderDirectionEnum;
use App\Enum\OrderablePropertyEnum;

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
    'title' => 'Заголовок',
    'url' => 'URL',
    'duplicates_count' => 'Количество дубликатов',

    'hash' => 'Хэш',
    'type' => 'Тип',
    'ext' => 'Расширение',
    'mime_type' => 'Мим тип',
    'hash_algo' => 'Алгоритм хэширования',

    'order_by' => 'Сортировать по',
    'order_direction' => 'Направление сортировки',
    'files_count' => 'Кол-во файлов',
    'source' => 'Источник',
    'source_identifier' => 'ID в источнике',
    'source_url' => 'URL источника',
    'reviewer_name' => 'Имя рецензента',

    'orders' => [
        OrderablePropertyEnum::ID->value => 'ID',
        OrderablePropertyEnum::TITLE->value => 'Заголовок',
        OrderablePropertyEnum::NAME->value => 'Название',
        OrderablePropertyEnum::SIZE->value => 'Размер',
        OrderablePropertyEnum::CREATED_AT->value => 'Дата создания',
        OrderablePropertyEnum::PATTERN_ID->value => 'ID выкройки',
    ],

    'order_directions' => [
        OrderDirectionEnum::DESC->value => 'По убыванию',
        OrderDirectionEnum::ASC->value => 'По возрастанию',
    ],

    'pattern_order' => [
        'default' => 'По умолчанию',
        PatternOrderEnum::DATE_ASC->value => 'По дате (от меньшего к большему)',
        PatternOrderEnum::DATE_DESC->value => 'По дате (от большего к меньшему)',
        PatternOrderEnum::RATING_DESC->value => 'По рейтингу (от большего к меньшему)',
    ],
];
