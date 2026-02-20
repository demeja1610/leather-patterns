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
        $category = $this->getCategory(id: $id);

        if (!$category instanceof PatternCategory) {
            return abort(code: Response::HTTP_NOT_FOUND);
        }

        $this->loadReplacement(category: $category);

        $categoryReplacements = $this->getCategoryReplacements(
            exceptId: $category->id,
        );

        return view(view: 'pages.admin.pattern-category.edit', data: [
            'category' => $category,
            'categoryReplacements' => $categoryReplacements,
        ]);
    }

    protected function getCategory($id): ?PatternCategory
    {
        return PatternCategory::query()->find(id: $id);
    }

    protected function loadReplacement(PatternCategory &$category): void
    {
        if ($category->replace_id !== null) {
            $category->load(relations: 'replacement');
        }
    }

    protected function getCategoryReplacements(int $exceptId): Collection
    {
        return PatternCategory::query()
            ->whereNull('replace_id')
            ->where('remove_on_appear', false)
            ->where(column: 'id', operator: '!=', value: $exceptId)
            ->select(columns: [
                'id',
                'name',
            ])->orderBy(column: 'name')->get();
    }
}
