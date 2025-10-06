<?php

namespace App\Http\Controllers;

use App\Models\Pattern;

class PatternSingleController extends Controller
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
