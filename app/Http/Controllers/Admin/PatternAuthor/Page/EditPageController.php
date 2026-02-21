<?php

declare(strict_types=1);

namespace App\Http\Controllers\Admin\PatternAuthor\Page;

use App\Models\PatternAuthor;
use App\Http\Controllers\Controller;
use Illuminate\Database\Eloquent\Collection;
use Symfony\Component\HttpFoundation\Response;

class EditPageController extends Controller
{
    public function __invoke($id)
    {
        $author = $this->getAuthor(id: $id);

        if (!$author instanceof PatternAuthor) {
            return abort(code: Response::HTTP_NOT_FOUND);
        }

        $this->loadReplacement(author: $author);

        $authorReplacements = $this->getAuthorReplacements(
            exceptId: $author->id,
        );

        return view(view: 'pages.admin.pattern-author.edit', data: [
            'author' => $author,
            'authorReplacements' => $authorReplacements,
        ]);
    }

    protected function getAuthor($id): ?PatternAuthor
    {
        return PatternAuthor::query()->find(id: $id);
    }

    protected function loadReplacement(PatternAuthor &$author): void
    {
        if ($author->replace_id !== null) {
            $author->load(relations: 'replacement');
        }
    }

    protected function getAuthorReplacements(int $exceptId): Collection
    {
        return PatternAuthor::query()
            ->whereNull('replace_id')
            ->where('remove_on_appear', false)
            ->where(column: 'id', operator: '!=', value: $exceptId)
            ->select(columns: [
                'id',
                'name',
            ])->orderBy(column: 'name')->get();
    }
}
