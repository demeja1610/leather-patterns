<?php

declare(strict_types=1);

namespace App\Enum;

enum OrderablePropertyEnum: string
{
    case ID = 'id';
    case TITLE = 'title';
    case NAME = 'name';
    case SIZE = 'size';
    case CREATED_AT = 'created_at';
}
