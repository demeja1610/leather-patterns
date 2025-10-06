<?php

declare(strict_types=1);

namespace App\Enum;

enum PatternOrderEnum: string
{
    case DATE_ASC = 'date_asc';
    case DATE_DESC = 'date_desc';
    case RATING_DESC = 'rating_desc';
}
