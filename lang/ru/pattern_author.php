<?php

declare(strict_types=1);

return [
    'pattern_authors' => 'Авторы выкроек',
    'creation' => 'Создание автора выкроек',
    'edition' => 'Редактирование автора выкроек',

    'id' => 'ID',
    'name' => 'Название',
    'patterns_count' => 'Кол-во выкроек',
    'replacement_for_count' => 'Кол-во замен',
    'created_at' => 'Дата создания',
    'replacement' => 'Заменить на',
    'remove_on_appear' => 'Удалять из выкроек при появлении',
    'remove_on_appear_short' => 'Удалять при появлении',
    'is_published' => 'Опубликован',
    'has_patterns' => 'Используется в выкройках',
    'has_replacement' => 'Есть замена',


    'admin' => [
        'created' => 'Автор выкройки: `:name` успешно создан',
        'updated' => 'Автор с ID: `:id` успешно обновлен',
        'failed_to_update' => 'Не получилось обновить автора выкройки с ID: `:id`',
        'single_delete_success' => 'Автор выкройки с навзванием: `:name` успешно удален',
        'single_failed_to_delete' => 'Не получилось удалить автора выкройки с названием: `:name`',
        'confirm_delete_text' => 'Пожалуйста, подтвердите удаление автора',
        'cannot_remove_and_replace_same_time' => 'Автор не может быть удален и заменен одновременно, пожалуйста, выберите один из вариантов',
        'author_isnt_deletable' => 'Автор с названием: `:name` не может быть удален',
    ],
];
