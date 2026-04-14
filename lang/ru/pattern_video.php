<?php

declare(strict_types=1);

use App\Enum\VideoSourceEnum;

return [
    'pattern_videos' => 'Видео выкроек',
    'creation' => 'Создание видео выкроек',
    'edition' => 'Редактирование видео выкроек',

    'id' => 'ID',
    'url' => 'URL',
    'source' => 'Источник',
    'source_identifier' => 'ID в источнике',
    'pattern' => 'Выкройка',
    'created_at' => 'Дата создания',
    'has_patterns' => 'Есть выкройки',

    'admin' => [
        'created' => 'Видео с URL: `:url` успешно создано',
        'updated' => 'Видео с URL: `:url` успешно обновлено',
        'failed_to_update' => 'Невозможно обновить видео с ID: `:id`',
        'single_delete_success' => 'Видео с URL: `:url` успешно удалено',
        'single_failed_to_delete' => 'Невозможно удалить видео с URL: `:url`',
        'confirm_delete_text' => 'Пожалуйста подтвердите удаление видео',
        'video_isnt_deletable' => 'Видео с URL `:url` не может быть удалено',
    ],

    'errors' => [
        'alredy_exists_for_pattern' => 'Такое URL уже существует для выбранной выкройки',
        'only_single_video_allowed' => 'Можнно добавлять только 1 URL за раз',
        'unknown_source_or_wrong_url' => 'Неизвестный источник видео или видео не найдено',
    ],

    'sources' => [
        VideoSourceEnum::VK->value => 'VK',
        VideoSourceEnum::YOUTUBE->value => 'Youtube',
    ],
];
