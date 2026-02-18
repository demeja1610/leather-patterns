<?php

declare(strict_types=1);

namespace App\Http\Controllers\Admin\PatternCategory\Page;

use App\Models\PatternCategory;
use App\Http\Controllers\Controller;
use Illuminate\Database\Eloquent\Collection;

class CreatePageController extends Controller
{
    public function __invoke()
    {
        $categoryReplacements = $this->getCategoryReplacements();

        return view('pages.admin.pattern-category.create', compact([
            'categoryReplacements',
        ]));
    }

    protected function getCategoryReplacements(): Collection
    {
        return PatternCategory::query()
            ->where('replace_id', null)
            ->select([
                'id',
                'name',
            ])->orderBy('name')->get();
    }
}
