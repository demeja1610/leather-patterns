<?php

declare(strict_types=1);

namespace App\Http\Controllers\Admin\PatternCategory\Page;

use App\Models\PatternCategory;
use Illuminate\Contracts\View\View;
use App\Http\Controllers\Controller;
use Illuminate\Database\Eloquent\Collection;

class CreatePageController extends Controller
{
    public function __invoke(): View
    {
        $categoryReplacements = $this->getCategoryReplacements();

        return view(view: 'pages.admin.pattern-category.create', data: [
            'categoryReplacements' => $categoryReplacements,
        ]);
    }

    protected function getCategoryReplacements(): Collection
    {
        return PatternCategory::query()
            ->whereNull('replace_id')
            ->select(columns: [
                'id',
                'name',
            ])->orderBy(column: 'name')->get();
    }
}
