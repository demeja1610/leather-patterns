<?php

declare(strict_types=1);

namespace App\Http\Controllers\Admin\PatternTag\Page;

use App\Models\PatternTag;
use App\Http\Controllers\Controller;
use App\Models\PatternAuthor;
use Illuminate\Database\Eloquent\Collection;
use Symfony\Component\HttpFoundation\Response;

class EditPageController extends Controller
{
    public function __invoke($id)
    {
        $tag = $this->getTag($id);

        if (!$tag) {
            return abort(Response::HTTP_NOT_FOUND);
        }

        $this->loadReplacement($tag);

        $this->loadAuthorReplacement($tag);

        $tagReplacements = $this->getTagReplacements(
            exceptId: $tag->id
        );

        $authorReplacements = $this->getAuthorReplacements();

        return view('pages.admin.pattern-tag.edit', compact([
            'tag',
            'tagReplacements',
            'authorReplacements',
        ]));
    }

    protected function getTag($id): ?PatternTag
    {
        return PatternTag::find($id);
    }

    protected function loadReplacement(PatternTag &$tag): void
    {
        if ($tag->replace_id !== null) {
            $tag->load('replacement');
        }
    }

    protected function loadAuthorReplacement(PatternTag &$tag): void
    {
        if ($tag->replace_author_id !== null) {
            $tag->load('authorReplacement');
        }
    }

    protected function getTagReplacements(int $exceptId): Collection
    {
        return PatternTag::query()
            ->where('replace_id', null)
            ->where('id', '!=', $exceptId)
            ->select([
                'id',
                'name',
            ])->orderBy('name')->get();
    }

    protected function getAuthorReplacements(): Collection
    {
        return PatternAuthor::query()
            ->select([
                'id',
                'name',
            ])->orderBy('name')->get();
    }
}
