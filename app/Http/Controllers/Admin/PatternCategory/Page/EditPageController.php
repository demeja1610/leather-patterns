<?php

declare(strict_types=1);

namespace App\Http\Controllers\Admin\PatternCategory\Page;

use App\Models\PatternCategory;
use App\Http\Controllers\Controller;
use Illuminate\Database\Eloquent\Collection;
use Symfony\Component\HttpFoundation\Response;

class EditPageController extends Controller
{
    public function __invoke($id)
    {
        $category = $this->getCategory($id);

        if (!$category) {
            return abort(Response::HTTP_NOT_FOUND);
        }

        $this->loadReplacement($category);

        $categoryReplacements = $this->getCategoryReplacements(
            exceptId: $category->id
        );

        return view('pages.admin.pattern-category.edit', compact([
            'category',
            'categoryReplacements',
        ]));
    }

    protected function getCategory($id): ?PatternCategory
    {
        return PatternCategory::find($id);
    }

    protected function loadReplacement(PatternCategory $category): void
    {
        if ($category->replace_id !== null) {
            $category->load('replacement');
        }
    }

    protected function getCategoryReplacements(int $exceptId): Collection
    {
        return PatternCategory::query()
            ->where('replace_id', null)
            ->where('id', '!=', $exceptId)
            ->select([
                'id',
                'name',
            ])->orderBy('name')->get();
    }
}
