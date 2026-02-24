<?php

declare(strict_types=1);

namespace App\Http\Controllers\Admin\PatternTag\Api\v1;

use Illuminate\Database\Eloquent\Builder;
use App\Http\Controllers\Admin\PatternTag\Api\v1\SearchController;

class SearchReplaceController extends SearchController
{
    protected function getBasePatternCategoryQuery(): Builder
    {
        return parent::getBasePatternTagQuery()
            ->whereNull('replace_id')
            ->whereNull('replace_author_id')
            ->whereNull('replace_category_id')
            ->where('remove_on_appear', false);
    }
}
