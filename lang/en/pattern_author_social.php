<?php

declare(strict_types=1);

use App\Enum\SocialTypeEnum;

return [
    'pattern_author_socials' => 'Author links',
    'creation' => 'Create author link',
    'edition' => 'Edit author link',

    'id' => 'ID',
    'type' => 'Type',
    'url' => 'URL',
    'is_published' => 'Is published',
    'author' => 'Author',
    'created_at' => 'Created at',

    'types' => [
        SocialTypeEnum::YOUTUBE->value => 'Youtube',
        SocialTypeEnum::INSTAGRAM->value => 'Instagram',
        SocialTypeEnum::VK->value => 'VK',
        SocialTypeEnum::SITE->value => 'Site',
        SocialTypeEnum::TELEGRAM->value => 'Telegram',
    ],

    'admin' => [
        'created' => 'Author link with type: `:type` successfully created',
        'updated' => 'Author link with ID: `:id` successfully updated',
        'failed_to_update' => 'Failed to update author link with ID: `:id`',
        'single_delete_success' => 'Author link with ID: `:id` successfully deleted',
        'single_failed_to_delete' => 'Failed to delete author link with ID: `:id`',
        'confirm_delete_text' => 'Please confirm author link delete',
        'social_isnt_deletable' => 'Author link with ID `:id` cannot be deleted',
        'url_type_mismatch' => 'URL is not belongs for selected type',
    ],
];
