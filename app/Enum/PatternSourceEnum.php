<?php

declare(strict_types=1);

namespace App\Enum;

enum PatternSourceEnum: string
{
    case NEOVIMA = 'neovima';
    case V_POMOSH_KOZHEVNIKU = 'v_pomosh_kozhevniku';
    case MLEATHER = 'mleather';
    case ABZALA = 'abzala';
    case PATTERN_HUB = 'patternhub';
    case FORMULA_KOZHI = 'formulakozhi';
    case SKINCUTS = 'skincuts';
    case PABLIK_KOZHEVNIKA = 'pablik_kozhevnika';
    case MYETSY = 'myetsy';
    case LASERBIZ = 'laserbiz';
    case FIRST_KOZHA = 'first_kozha';
    case SKINPAT = 'skinpat';
    case LEATHER_PATTERNS = 'leather_patterns';
    case CUTME = 'cutme';
    case LOCAL = 'local';
}
