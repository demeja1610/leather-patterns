<?php

declare(strict_types=1);

use App\Enum\SocialTypeEnum;

return [
    'pattern_author_socials' => 'Ссылки на автора',
    'creation' => 'Создание сслыки на автора',
    'edition' => 'Редактирование ссылки на автора',

    'id' => 'ID',
    'type' => 'Тип',
    'url' => 'URL',
    'is_published' => 'Опубликована',
    'author' => 'Автор',
    'created_at' => 'Дата создания',

    'types' => [
        SocialTypeEnum::YOUTUBE->value => 'Youtube',
        SocialTypeEnum::INSTAGRAM->value => 'Instagram',
        SocialTypeEnum::VK->value => 'VK',
        SocialTypeEnum::SITE->value => 'Сайт',
        SocialTypeEnum::TELEGRAM->value => 'Telegram',
    ],

    'admin' => [
        'created' => 'Ссылка на автора с типом: `:type` успешно создана',
        'updated' => 'Ссылка на автора c ID: `:id` успешно обновлена',
        'failed_to_update' => 'Не получилось обновить ссылку на автора с ID: `:id`',
        'single_delete_success' => 'Ссылка на автора с ID: `:id` успешно удалена',
        'single_failed_to_delete' => 'Не получилось удалить ссылку на автора с ID: `:id`',
        'confirm_delete_text' => 'Пожалуйста, подтвердите удаление ссылки на автора',
        'social_isnt_deletable' => 'Ссылка на автора с ID `:id` не может быть удалена',
        'url_type_mismatch' => 'URL не пренадлежит к выбранному типу',
    ],
];
