<?php

declare(strict_types=1);

namespace App\Http\Controllers\Admin\PatternAuthor\Page;

use App\Models\PatternAuthor;
use Illuminate\Contracts\View\View;
use App\Http\Controllers\Controller;
use Illuminate\Database\Eloquent\Collection;

class CreatePageController extends Controller
{
    public function __invoke(): View
    {
        $authorReplacements = $this->getAuthorReplacements();

        return view(view: 'pages.admin.pattern-author.create', data: [
            'authorReplacements' => $authorReplacements,
        ]);
    }

    protected function getAuthorReplacements(): Collection
    {
        return PatternAuthor::query()
            ->whereNull('replace_id')
            ->where('remove_on_appear', false)
            ->select(columns: [
                'id',
                'name',
            ])->orderBy(column: 'name')->get();
    }
}
