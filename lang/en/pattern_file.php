<?php

use App\Enum\FileTypeEnum;

return [
    'files' => 'Files',
    'id' => 'ID',
    'type' => 'File type',
    'ext' => 'File extension',
    'mime_type' => 'File mime_type',
    'hash_algo' => 'Hash algorithm name',
    'pattern' => 'Pattern',
    'hash' => 'File hash',
    'duplicates' => 'Duplicated files',
    'duplicates_count' => 'Duplicates count',
    'public_pattern_links' => 'Public links',
    'admin_pattern_links' => 'Admin links',
    'mb_size' => 'Size (MByte)',
    'mb' => 'MByte',
    'pattern_id' => 'Pattern ID',
    'download' => 'Download',

    'types' => [
        FileTypeEnum::IMAGE->value => 'Image',
        FileTypeEnum::ARCHIVE->value => 'Archive',
        FileTypeEnum::PDF->value => 'PDF file',
        FileTypeEnum::CAD->value => 'CAD file',
        FileTypeEnum::VECTOR->value => 'Vector graphic file',
    ],
];
