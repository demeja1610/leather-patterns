<?php

declare(strict_types=1);

namespace App\Http\Controllers\Admin\PatternCategory\Api\v1;

use Illuminate\Database\Eloquent\Builder;
use App\Http\Controllers\Admin\PatternCategory\Api\v1\SearchController;

class SearchReplaceController extends SearchController
{
    protected function getBasePatternCategoryQuery(): Builder
    {
        return parent::getBasePatternCategoryQuery()
            ->whereNull('replace_id')
            ->where('remove_on_appear', false);
    }
}
