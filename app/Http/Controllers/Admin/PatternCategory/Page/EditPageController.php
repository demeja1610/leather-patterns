<?php

declare(strict_types=1);

namespace App\Http\Controllers\Admin\PatternCategory\Page;

use App\Models\PatternCategory;
use App\Http\Controllers\Controller;
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

        return view(view: 'pages.admin.pattern-category.edit', data: [
            'category' => $category,
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
}
