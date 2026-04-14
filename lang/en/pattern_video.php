<?php

declare(strict_types=1);

use App\Enum\VideoSourceEnum;

return [
    'pattern_videos' => 'Pattern videos',
    'creation' => 'Create pattern video',
    'edition' => 'Edit pattern video',

    'id' => 'ID',
    'url' => 'URL',
    'source' => 'Source',
    'source_identifier' => 'Source ID',
    'pattern' => 'Pattern',
    'created_at' => 'Created at',
    'has_patterns' => 'Has patterns',

    'admin' => [
        'created' => 'Video with url: `:url` successfully created',
        'updated' => 'Video with url: `:url` successfully updated',
        'failed_to_update' => 'Failed to update video with id: `:id`',
        'single_delete_success' => 'Video with url: `:url` successfully deleted',
        'single_failed_to_delete' => 'Failed to delete video with url: `:url`',
        'confirm_delete_text' => 'Please confirm video delete',
        'video_isnt_deletable' => 'Video with url :url cannot be deleted',
    ],

    'errors' => [
        'alredy_exists_for_pattern' => 'This URL already exists for selected pattern',
        'only_single_video_allowed' => 'Allowed only URL for single video',
        'unknown_source_or_wrong_url' => 'Unknown video source or no video found in this URL',
    ],

    'sources' => [
        VideoSourceEnum::VK->value => 'VK',
        VideoSourceEnum::YOUTUBE->value => 'Youtube',
    ],
];
