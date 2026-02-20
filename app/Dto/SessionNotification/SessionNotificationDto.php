<?php

declare(strict_types=1);

namespace App\Dto\SessionNotification;

use App\Dto\Dto;
use App\Enum\NotificationTypeEnum;

class SessionNotificationDto extends Dto
{
    public function __construct(
        protected readonly string $text,
        protected readonly NotificationTypeEnum $type = NotificationTypeEnum::INFO,
    ) {}

    public static function fromArray(array $data): static
    {
        return new static(
            text: $data['text'],
            type: NotificationTypeEnum::from($data['type']),
        );
    }

    public function toArray(): array
    {
        return [
            'text' => $this->text,
            'type' => $this->type->value,
        ];
    }

    public function getText(): string
    {
        return $this->text;
    }

    public function getType(): NotificationTypeEnum
    {
        return $this->type;
    }
}
