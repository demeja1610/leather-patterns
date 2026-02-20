<?php

declare(strict_types=1);

namespace App\Http\Controllers\Pattern\Web\v1;

use App\Models\Pattern;
use Illuminate\Routing\Controller;
use Illuminate\Contracts\View\View;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class SingleController extends Controller
{
    public function __invoke(int $id): View
    {
        $pattern = $this->getPattern(id: $id);

        if (!$pattern instanceof Pattern) {
            abort(code: 404);
        }

        return view(view: 'pages.pattern.single', data: [
            'pattern' => $pattern,
        ]);
    }

    protected function getPattern(int $id): ?Pattern
    {

        $q = Pattern::query()
            ->where('id', $id)
            ->with(
                relations: [
                    'categories' => function (BelongsToMany $sq): BelongsToMany {
                        $table = $sq->getRelated()->getTable();

                        $sq->where('is_published', true);

                        $sq->select([
                            "{$table}.id",
                            "{$table}.name",
                        ]);

                        return $sq;
                    },
                    'tags' => function (BelongsToMany $sq): BelongsToMany {
                        $table = $sq->getRelated()->getTable();

                        $sq->where('is_published', true);

                        $sq->select([
                            "{$table}.id",
                            "{$table}.name",
                        ]);

                        return $sq;
                    },
                    'author' => function (BelongsTo $sq): BelongsTo {
                        $table = $sq->getRelated()->getTable();

                        // $sq->where('is_published', true);

                        $sq->select([
                            "{$table}.id",
                            "{$table}.name",
                        ]);

                        return $sq;
                    },
                    'images' => function (HasMany $sq): HasMany {
                        $table = $sq->getRelated()->getTable();

                        $sq->select([
                            "{$table}.id",
                            "{$table}.path",
                            "{$table}.pattern_id",
                        ]);

                        return $sq;
                    },
                    'files' => function (HasMany $sq): HasMany {
                        $table = $sq->getRelated()->getTable();

                        $sq->select([
                            "{$table}.id",
                            "{$table}.path",
                            "{$table}.type",
                            "{$table}.pattern_id",
                        ]);

                        return $sq;
                    },
                    'reviews'  => function (HasMany $sq): HasMany {
                        $table = $sq->getRelated()->getTable();

                        // $sq->where('is_approved', true);

                        $sq->select([
                            "{$table}.id",
                            "{$table}.reviewer_name",
                            "{$table}.rating",
                            "{$table}.comment",
                            "{$table}.reviewed_at",
                            "{$table}.pattern_id",
                        ]);

                        return $sq;
                    },
                    'videos' => function (HasMany $sq): HasMany {
                        $table = $sq->getRelated()->getTable();

                        $sq->select([
                            "{$table}.id",
                            "{$table}.source",
                            "{$table}.source_identifier",
                            "{$table}.pattern_id",
                        ]);

                        return $sq;
                    },
                ],
            );

        return $q->first();
    }
}
