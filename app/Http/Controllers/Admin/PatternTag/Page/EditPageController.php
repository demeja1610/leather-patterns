<?php

declare(strict_types=1);

namespace App\Http\Controllers\Admin\PatternTag\Page;

use App\Models\PatternTag;
use App\Http\Controllers\Controller;
use Symfony\Component\HttpFoundation\Response;

class EditPageController extends Controller
{
    public function __invoke($id)
    {
        $tag = $this->getTag(id: $id);

        if (!$tag instanceof PatternTag) {
            return abort(code: Response::HTTP_NOT_FOUND);
        }

        $this->loadReplacement(tag: $tag);

        $this->loadAuthorReplacement(tag: $tag);

        $this->loadCategoryReplacement(tag: $tag);

        return view('pages.admin.pattern-tag.edit', [
            'tag' => $tag,
        ]);
    }

    protected function getTag($id): ?PatternTag
    {
        return PatternTag::query()->find(id: $id);
    }

    protected function loadReplacement(PatternTag &$tag): void
    {
        if ($tag->replace_id !== null) {
            $tag->load(relations: 'replacement');
        }
    }

    protected function loadAuthorReplacement(PatternTag &$tag): void
    {
        if ($tag->replace_author_id !== null) {
            $tag->load(relations: 'authorReplacement');
        }
    }

    protected function loadCategoryReplacement(PatternTag &$tag): void
    {
        if ($tag->replace_category_id !== null) {
            $tag->load(relations: 'categoryReplacement');
        }
    }
}
