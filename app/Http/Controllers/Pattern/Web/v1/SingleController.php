<?php

declare(strict_types=1);

namespace App\Http\Controllers\Pattern\Web\v1;

use App\Models\Pattern;
use Illuminate\Routing\Controller;
use Illuminate\Contracts\View\View;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class SingleController extends Controller
{
    public function __invoke(int $id): View
    {
        $pattern = $this->getPattern($id);

        if (!$pattern instanceof Pattern) {
            abort(404);
        }

        return view('pages.pattern.single', [
            'pattern' => $pattern
        ]);
    }

    protected function getPattern(int $id): ?Pattern
    {
        $q = Pattern::query()
            ->where('id', $id)
            ->with([
                'categories' => function (BelongsToMany $sq): BelongsToMany {
                    $table = $sq->getRelated()->getTable();

                    $sq->where('is_published', true);

                    $sq->select([
                        "{$table}.id",
                        "{$table}.name"
                    ]);

                    return $sq;
                },
                'tags',
                'author',
                'images',
                'files',
                'reviews',
                'videos',
            ]);

        return $q->first();
    }
}
