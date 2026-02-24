<?php

declare(strict_types=1);

namespace App\Http\Controllers\Admin\PatternAuthor\Page;

use App\Models\PatternAuthor;
use App\Http\Controllers\Controller;
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

        return view(view: 'pages.admin.pattern-author.edit', data: [
            'author' => $author,
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
}
