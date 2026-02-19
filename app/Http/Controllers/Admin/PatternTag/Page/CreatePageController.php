<?php

declare(strict_types=1);

namespace App\Http\Controllers\Admin\PatternTag\Page;

use App\Http\Controllers\Controller;
use App\Models\PatternAuthor;
use App\Models\PatternTag;
use Illuminate\Database\Eloquent\Collection;

class CreatePageController extends Controller
{
    public function __invoke()
    {
        $tagReplacements = $this->getTagReplacements();
        $authorReplacements = $this->getAuthorReplacements();

        return view('pages.admin.pattern-tag.create', compact([
            'tagReplacements',
            'authorReplacements',
        ]));
    }

    protected function getTagReplacements(): Collection
    {
        return PatternTag::query()
            ->where('replace_id', null)
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
