<?php

declare(strict_types=1);

namespace App\Http\Controllers\Admin\PatternTag\Page;

use App\Models\PatternTag;
use App\Models\PatternAuthor;
use App\Http\Controllers\Controller;
use Illuminate\Database\Eloquent\Collection;
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

        $tagReplacements = $this->getTagReplacements(
            exceptId: $tag->id
        );

        $authorReplacements = $this->getAuthorReplacements();

        return view(view: 'pages.admin.pattern-tag.edit', data: [
            'tag' => $tag,
            'tagReplacements' => $tagReplacements,
            'authorReplacements' => $authorReplacements
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

    protected function getTagReplacements(int $exceptId): Collection
    {
        return PatternTag::query()
            ->whereNull('replace_id')
            ->where(column: 'id', operator: '!=', value: $exceptId)
            ->select(columns: [
                'id',
                'name',
            ])->orderBy(column: 'name')->get();
    }

    protected function getAuthorReplacements(): Collection
    {
        return PatternAuthor::query()
            ->select([
                'id',
                'name',
            ])->orderBy(column: 'name')->get();
    }
}
