<?php

declare(strict_types=1);

namespace App\Http\Controllers\Admin\PatternAuthor\Api\v1;

use Illuminate\Database\Eloquent\Builder;
use App\Http\Controllers\Admin\PatternAuthor\Api\v1\SearchController;

class SearchReplaceController extends SearchController
{
    protected function getBasePatternAuthorQuery(): Builder
    {
        return parent::getBasePatternAuthorQuery()
            ->whereNull('replace_id')
            ->where('remove_on_appear', false);
    }
}
