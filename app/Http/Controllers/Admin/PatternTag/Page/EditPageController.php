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
        $tag = $this->getTag($id);

        if (!$tag instanceof PatternTag) {
            return abort(Response::HTTP_NOT_FOUND);
        }

        $this->loadReplacement($tag);

        $this->loadAuthorReplacement($tag);

        $tagReplacements = $this->getTagReplacements(
            exceptId: $tag->id
        );

        $authorReplacements = $this->getAuthorReplacements();

        return view('pages.admin.pattern-tag.edit', [
            'tag' => $tag,
            'tagReplacements' => $tagReplacements,
            'authorReplacements' => $authorReplacements
        ]);
    }

    protected function getTag($id): ?PatternTag
    {
        return PatternTag::query()->find($id);
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
            ->whereNull('replace_id')
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
