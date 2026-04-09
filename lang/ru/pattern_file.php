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

    'types' => [
        FileTypeEnum::IMAGE->value => 'Изображение',
        FileTypeEnum::ARCHIVE->value => 'Архив',
        FileTypeEnum::PDF->value => 'PDF файл',
        FileTypeEnum::DWG->value => 'CAD файл',
        FileTypeEnum::SVG->value => 'Файл векторной графики',
    ],
];
