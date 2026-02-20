<?php

declare(strict_types=1);
return [
    'pattern_tags' => 'Теги выкроек',
    'creation' => 'Создание тега выкроек',
    'edition' => 'Редактирование тега выкроек',

    'id' => 'ID',
    'name' => 'Название',
    'patterns_count' => 'Кол-во выкроек',
    'replacement_for_count' => 'Кол-во замен',
    'created_at' => 'Дата создания',
    'replacement' => 'Заменить на',
    'author_replacement' => 'Заменить на автора',
    'category_replacement' => 'Заменить на категорию',
    'remove_on_appear' => 'Удалять из выкроек при появлении',
    'remove_on_appear_short' => 'Удалять при появлении',
    'is_published' => 'Опубликован',
    'has_patterns' => 'Используется в выкройках',
    'has_replacement' => 'Есть замена',
    'has_author_replacement' => 'Есть замена на автора',
    'has_category_replacement' => 'Есть замена на категорию',

    'admin' => [
        'created' => 'Тег выкройки: `:name` успешно создан',
        'updated' => 'Тег с ID: `:id` успешно обновлен',
        'failed_to_update' => 'Не получилось обновить тег выкройки с ID: `:id`',
        'single_delete_success' => 'Тег выкройки с навзванием: `:name` успешно удален',
        'single_failed_to_delete' => 'Не получилось удалить тег выкройки с названием: `:name`',
        'success_mass_deleted' => ':count тегов выкроек было удалено',
        'patterns_not_empty' => 'Тег с названием: `:name` не может быть удален, потому что он используется в :count выкройках',
        'tag_needed_for_replace_or_remove' => 'Тег с названием: `:name` не может быть удален, потому что он нужен системе для замены или удаления в выкройках при появлении',
        'tag_is_replacement_for' => 'Тег с названием: `:name` не может быть удален, потому что является заменой для :count других тегов',
        'confirm_delete_text' => 'Пожалуйста, подтвердите удаление тега',
        'cannot_remove_and_replace_same_time' => 'Тег не может быть удален и заменен одновременно, пожалуйста, выберите один из вариантов',
        'cannot_replace_to_multiple' => 'Тег не может быть заменен несколько сущностей одновременно, выберите один из вариантов',
        'tag_isnt_deletable' => 'Тег с названием: `:name` не может быть удален',
    ],
];
