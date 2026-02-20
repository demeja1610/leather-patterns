<?php

declare(strict_types=1);

namespace App\Http\Controllers\Admin\PatternTag\Page;

use App\Models\PatternTag;
use App\Models\PatternAuthor;
use App\Models\PatternCategory;
use Illuminate\Contracts\View\View;
use App\Http\Controllers\Controller;
use Illuminate\Database\Eloquent\Collection;

class CreatePageController extends Controller
{
    public function __invoke(): View
    {
        $tagReplacements = $this->getTagReplacements();
        $authorReplacements = $this->getAuthorReplacements();
        $categoryReplacements = $this->getCategoryReplacements();

        return view(view: 'pages.admin.pattern-tag.create', data: [
            'tagReplacements' => $tagReplacements,
            'authorReplacements' => $authorReplacements,
            'categoryReplacements' => $categoryReplacements,
        ]);
    }

    protected function getTagReplacements(): Collection
    {
        return PatternTag::query()
            ->whereNull('replace_id')
            ->where('remove_on_appear', false)
            ->select(columns: [
                'id',
                'name',
            ])->orderBy(column: 'name')->get();
    }

    protected function getAuthorReplacements(): Collection
    {
        return PatternAuthor::query()
            ->select([
                'id',
                'name',
            ])->orderBy(column: 'name')->get();
    }

    protected function getCategoryReplacements(): Collection
    {
        return PatternCategory::query()
            ->whereNull('replace_id')
            ->where('remove_on_appear', false)
            ->select([
                'id',
                'name',
            ])->orderBy(column: 'name')->get();
    }
}
