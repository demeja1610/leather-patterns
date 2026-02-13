<?php

namespace App\Http\Controllers\Pattern\Web\v1;

use App\Models\Pattern;
use Illuminate\Routing\Controller;

class SingleController extends Controller
{
    public function __invoke(int $id)
    {
        $pattern = $this->getPattern($id);

        if (!$pattern) {
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
                'categories',
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
