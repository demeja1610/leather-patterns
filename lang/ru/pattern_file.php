<?php

use App\Enum\FileTypeEnum;

return [
    'files' => 'Файлы',
    'id' => 'ID',
    'type' => 'Тип файла',
    'ext' => 'Расширение файла',
    'mime_type' => 'Мим тип файла',
    'hash_algo' => 'Алгоритм хэширования',
    'pattern' => 'Выкройка',
    'hash' => 'Хэш файла',
    'duplicates' => 'Дубликаты файлов',
    'duplicates_count' => 'Кол-во дубликатов',
    'public_pattern_links' => 'Публичные ссылки',
    'admin_pattern_links' => 'Ссылки на админ панель',
    'mb_size' => 'Размер (МБайт)',
    'mb' => 'МБайт',
    'pattern_id' => 'ID выкройки',
    'download' => 'Скачать',

    'types' => [
        FileTypeEnum::IMAGE->value => 'Изображение',
        FileTypeEnum::ARCHIVE->value => 'Архив',
        FileTypeEnum::PDF->value => 'PDF файл',
        FileTypeEnum::CAD->value => 'CAD файл',
        FileTypeEnum::VECTOR->value => 'Файл векторной графики',
    ],

    'admin' => [
        'single_delete_success' => 'Файл выкройки с ID: `:id` успешно удален',
        'single_failed_to_delete' => 'Не получилось удалить файл выкройки с ID: `:id`',
        'confirm_delete_text' => 'Пожалуйста, подтвердите удаление файла выкройки.',
    ],
];
