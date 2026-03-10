<?php

declare(strict_types=1);

namespace App\Http\Controllers\Admin\PatternTag\Page;

use Carbon\Carbon;
use App\Models\PatternTag;
use App\Models\PatternAuthor;
use App\Models\PatternCategory;
use Illuminate\Contracts\View\View;
use App\Http\Controllers\Controller;
use Illuminate\Database\Eloquent\Builder;
use App\Http\Requests\Admin\PatternTag\ListRequest;

class ListPageController extends Controller
{
    protected array $activeFilters = [];
    protected array $extraData = [];

    public function __invoke(ListRequest $request): View
    {
        $tags = $this->getTags(request: $request);

        return view(view: 'pages.admin.pattern-tag.list', data: [
            'activeFilters' => $this->activeFilters,
            'extraData' => $this->extraData,
            'tags' => $tags,
        ]);
    }

    protected function getTags(ListRequest &$request)
    {
        $cursor = $request->input(key: 'cursor');

        $q = PatternTag::query();

        $this->applyFilters(
            request: $request,
            query: $q,
        );

        $q->withCount(relations: [
            'patterns',
            'replacementFor',
            'replacementForCategories',
        ]);

        $q->with(relations: [
            'replacement',
            'authorReplacement',
        ]);

        return $q->orderBy('id', 'desc')->cursorPaginate(
            perPage: 30,
            cursor: $cursor,
        )->withQueryString();
    }

    protected function applyFilters(ListRequest &$request, Builder &$query): void
    {
        $id = $request->input(key: 'id');

        if ($id !== null) {
            $this->activeFilters['id'] = $id;

            $query->where('id', $id);
        }

        $name = $request->input(key: 'name');

        if ($name !== null) {
            $this->activeFilters['name'] = $name;

            $query->where('name', 'LIKE', "%{$name}%");
        }

        $olderThanStr = $request->input(key: 'older_than');

        if ($olderThanStr !== null) {
            $olderThan = Carbon::parse(time: $olderThanStr);

            $this->activeFilters['older_than'] = $olderThan;

            $query->where('created_at', '<', $olderThan);
        }

        $newerThanStr = $request->input(key: 'newer_than');

        if ($newerThanStr !== null) {
            $newerThan = Carbon::parse(time: $newerThanStr);

            $this->activeFilters['newer_than'] = $newerThan;

            $query->where('created_at', '>', $newerThan);
        }

        $hasPatterns = $request->input(key: 'has_patterns');

        if ($hasPatterns !== null) {
            $this->activeFilters['has_patterns'] = (bool) $hasPatterns;

            if ((bool) $hasPatterns) {
                $query->whereHas(relation: 'patterns');
            } else {
                $query->whereDoesntHave(relation: 'patterns');
            }
        }

        $isPublished = $request->input(key: 'is_published');

        if ($isPublished !== null) {
            $this->activeFilters['is_published'] = (bool) $isPublished;

            if ((bool) $isPublished) {
                $query->where('is_published', true);
            } else {
                $query->where('is_published', false);
            }
        }

        $hasReplacement = $request->input(key: 'has_replacement');

        if ($hasReplacement !== null) {
            $this->activeFilters['has_replacement'] = (bool) $hasReplacement;

            if ((bool) $hasReplacement) {
                $query->whereNotNull('replace_id');
            } else {
                $query->whereNull('replace_id');
            }
        }

        $replaceToTagId = $request->input('replace_to_tag_id');

        if ($replaceToTagId !== null) {
            $this->activeFilters['replace_to_tag_id'] = $replaceToTagId;

            $this->extraData['replace_to_tag'] =  $this->getTag(id: $replaceToTagId);

            $query->where('replace_id', $replaceToTagId);
        }

        $hasAuthorReplacement = $request->input(key: 'has_author_replacement');

        if ($hasAuthorReplacement !== null) {
            $this->activeFilters['has_author_replacement'] = (bool) $hasAuthorReplacement;

            if ((bool) $hasAuthorReplacement) {
                $query->whereNotNull('replace_author_id');
            } else {
                $query->whereNull('replace_author_id');
            }
        }

        $replaceToAuthorId = $request->input('replace_to_author_id');

        if ($replaceToAuthorId !== null) {
            $this->activeFilters['replace_to_author_id'] = $replaceToAuthorId;

            $this->extraData['replace_to_author'] =  $this->getAuthor(id: $replaceToAuthorId);

            $query->where('replace_author_id', $replaceToAuthorId);
        }

        $hasCategoryReplacement = $request->input(key: 'has_category_replacement');

        if ($hasCategoryReplacement !== null) {
            $this->activeFilters['has_category_replacement'] = (bool) $hasCategoryReplacement;

            if ((bool) $hasCategoryReplacement) {
                $query->whereNotNull('replace_category_id');
            } else {
                $query->whereNull('replace_category_id');
            }
        }

        $replaceToCategoryId = $request->input('replace_to_category_id');

        if ($replaceToCategoryId !== null) {
            $this->activeFilters['replace_to_category_id'] = $replaceToCategoryId;

            $this->extraData['replace_to_category'] =  $this->getCategory(id: $replaceToCategoryId);

            $query->where('replace_category_id', $replaceToCategoryId);
        }

        $removeOnAppear = $request->input(key: 'remove_on_appear');

        if ($removeOnAppear !== null) {
            $this->activeFilters['remove_on_appear'] = (bool) $removeOnAppear;

            if ((bool) $removeOnAppear) {
                $query->where('remove_on_appear', true);
            } else {
                $query->where('remove_on_appear', false);
            }
        }
    }

    protected function getTag($id): ?PatternTag
    {
        return PatternTag::query()
            ->where('id', $id)
            ->select([
                'id',
                'name',
            ])
            ->first();
    }

    protected function getCategory($id): ?PatternCategory
    {
        return PatternCategory::query()
            ->where('id', $id)
            ->select([
                'id',
                'name',
            ])
            ->first();
    }

    protected function getAuthor($id): ?PatternAuthor
    {
        return PatternAuthor::query()
            ->where('id', $id)
            ->select([
                'id',
                'name',
            ])
            ->first();
    }
}
