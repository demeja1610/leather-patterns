<?php

declare(strict_types=1);

namespace App\Http\Controllers\Admin\Pattern\Page;

use App\Models\Pattern;
use App\Enum\PatternSourceEnum;
use App\Http\Controllers\Controller;
use Symfony\Component\HttpFoundation\Response;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class EditPageController extends Controller
{
    public function __invoke($id)
    {
        $pattern = $this->getPattern(id: $id);

        if (!$pattern instanceof Pattern) {
            return abort(code: Response::HTTP_NOT_FOUND);
        }

        $this->loadRelations(pattern: $pattern);

        $sources = PatternSourceEnum::cases();

        return view(view: 'pages.admin.pattern.edit', data: [
            'pattern' => $pattern,
            'sources' => $sources,
        ]);
    }

    protected function getPattern($id): ?Pattern
    {
        return Pattern::query()->find(id: $id);
    }

    protected function loadRelations(Pattern &$pattern): void
    {
        if ($pattern->author_id !== null) {
            $pattern->load([
                'author' => function (BelongsTo $sq): void {
                    $table = $sq->getRelated()->getTable();

                    $sq->select([
                        "{$table}.id",
                        "{$table}.name",
                    ]);
                },
            ]);
        }

        $pattern->load([
            'categories' => function (BelongsToMany $sq): void {
                $table = $sq->getRelated()->getTable();

                $sq->select([
                    "{$table}.id",
                    "{$table}.name",
                ]);
            },
            'tags' => function (BelongsToMany $sq): void {
                $table = $sq->getRelated()->getTable();

                $sq->select([
                    "{$table}.id",
                    "{$table}.name",
                ]);
            },
        ]);
    }
}
