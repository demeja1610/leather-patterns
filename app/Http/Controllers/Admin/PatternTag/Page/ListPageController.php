<?php

declare(strict_types=1);

namespace App\Http\Controllers\Admin\PatternTag\Page;

use Carbon\Carbon;
use App\Models\PatternTag;
use Illuminate\Contracts\View\View;
use App\Http\Controllers\Controller;
use Illuminate\Database\Eloquent\Builder;
use App\Http\Requests\Admin\PatternTag\ListRequest;

class ListPageController extends Controller
{
    protected array $activeFilters = [];

    public function __invoke(ListRequest $request): View
    {
        $tags = $this->getTags($request);

        return view('pages.admin.pattern-tag.list', [
            'activeFilters' => $this->activeFilters,
            'tags' => $tags,
        ]);
    }

    protected function getTags(ListRequest &$request)
    {
        $cursor = $request->get('cursor');

        $q = PatternTag::query();

        $this->applyFilters(
            request: $request,
            query: $q,
        );

        $q->withCount([
            'patterns',
            'replacementFor',
        ]);

        $q->with([
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
        $id = $request->get('id');

        if ($id !== null) {
            $this->activeFilters['id'] = $id;

            $query->where('id', $id);
        }

        $name = $request->get('name');

        if ($name !== null) {
            $this->activeFilters['name'] = $name;

            $query->where('name', 'LIKE', "%{$name}%");
        }

        $olderThanStr = $request->get('older_than');

        if ($olderThanStr !== null) {
            $olderThan = Carbon::parse($olderThanStr);

            $this->activeFilters['older_than'] = $olderThan;

            $query->where('created_at', '<', $olderThan);
        }

        $newerThanStr = $request->get('newer_than');

        if ($newerThanStr !== null) {
            $newerThan = Carbon::parse($newerThanStr);

            $this->activeFilters['newer_than'] = $newerThan;

            $query->where('created_at', '>', $newerThan);
        }

        $hasPatterns = $request->get('has_patterns');

        if ($hasPatterns !== null) {
            $this->activeFilters['has_patterns'] = (bool) $hasPatterns;

            if ((bool) $hasPatterns) {
                $query->whereHas('patterns');
            } else {
                $query->whereDoesntHave('patterns');
            }
        }

        $isPublished = $request->get('is_published');

        if ($isPublished !== null) {
            $this->activeFilters['is_published'] = (bool) $isPublished;

            if ((bool) $isPublished) {
                $query->where('is_published', true);
            } else {
                $query->where('is_published', false);
            }
        }

        $hasReplacement = $request->get('has_replacement');

        if ($hasReplacement !== null) {
            $this->activeFilters['has_replacement'] = (bool) $hasReplacement;

            if ((bool) $hasReplacement) {
                $query->whereNotNull('replace_id');
            } else {
                $query->whereNull('replace_id');
            }
        }

        $hasAuthorReplacement = $request->get('has_author_replacement');

        if ($hasAuthorReplacement !== null) {
            $this->activeFilters['has_author_replacement'] = (bool) $hasAuthorReplacement;

            if ((bool) $hasAuthorReplacement) {
                $query->whereNotNull('replace_author_id');
            } else {
                $query->whereNull('replace_author_id');
            }
        }

        $removeOnAppear = $request->get('remove_on_appear');

        if ($removeOnAppear !== null) {
            $this->activeFilters['remove_on_appear'] = (bool) $removeOnAppear;

            if ((bool) $removeOnAppear) {
                $query->where('remove_on_appear', true);
            } else {
                $query->where('remove_on_appear', false);
            }
        }
    }
}
