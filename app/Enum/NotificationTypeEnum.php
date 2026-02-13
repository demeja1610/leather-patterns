<?php

declare(strict_types=1);

namespace App\Enum;

enum NotificationTypeEnum: string
{
    case INFO = 'info';
    case SUCCESS = 'success';
    case WARNING = 'warning';
    case ERROR = 'error';
}
