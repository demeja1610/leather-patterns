<?php

declare(strict_types=1);

namespace App\Http\Controllers\Admin\PatternVideo\Page;

use Carbon\Carbon;
use App\Models\Pattern;
use App\Models\PatternVideo;
use App\Enum\VideoSourceEnum;
use Illuminate\Support\Facades\DB;
use Illuminate\Contracts\View\View;
use App\Http\Controllers\Controller;
use Illuminate\Database\Eloquent\Builder;
use App\Http\Requests\Admin\PatternVideo\ListRequest;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

class ListPageController extends Controller
{
    protected array $activeFilters = [];
    protected array $extraData = [];
    protected readonly PatternVideo $newPatternVideo;

    public function __construct()
    {
        $this->newPatternVideo = new PatternVideo();
    }


    public function __invoke(ListRequest $request): View
    {
        $videos = $this->getVideos(request: $request);
        $sources = $this->getSources();

        return view(view: 'pages.admin.pattern-video.list', data: [
            'activeFilters' => $this->activeFilters,
            'extraData' => $this->extraData,
            'videos' => $videos,
            'sources' => $sources,
        ]);
    }

    protected function getVideos(ListRequest &$request): LengthAwarePaginator
    {
        $page = $request->input(key: 'page');

        $q = PatternVideo::query();

        $this->applyFilters(
            request: $request,
            query: $q,
        );

        $q->with([
            'pattern',
        ]);

        return $q->orderBy('id', 'desc')->paginate(
            perPage: 30,
            page: $page,
        )->withQueryString();
    }

    protected function applyFilters(ListRequest &$request, Builder &$query): void
    {
        $id = $request->input(key: 'id');

        if ($id !== null) {
            $this->activeFilters['id'] = $id;

            $query->where('id', $id);
        }

        $url = $request->input(key: 'url');

        if ($url !== null) {
            $this->activeFilters['url'] = $url;

            $query->where('url', 'LIKE', "%{$url}%");
        }

        $sourceStr = $request->input('source');

        if ($sourceStr !== null) {
            $source = VideoSourceEnum::tryFrom($sourceStr);

            if ($source !== null) {
                $this->activeFilters['source'] = $sourceStr;

                $query->where('source', $source->value);
            }
        }

        $sourceIdentifier = $request->input(key: 'source_identifier');

        if ($sourceIdentifier !== null) {
            $this->activeFilters['source_identifier'] = $sourceIdentifier;

            $query->where('source_identifier', 'LIKE', "%{$sourceIdentifier}%");
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
                $query->whereHas(relation: 'pattern');
            } else {
                $query->whereDoesntHave(relation: 'pattern');
            }
        }

        $patternId = $request->input('pattern_id');

        if ($patternId !== null) {
            $this->activeFilters['pattern_id'] = $patternId;
            $this->extraData['selected_pattern'] = $this->getPattern(id: $patternId);

            $query->where('pattern_id', $patternId);
        }
    }

    protected function getPattern($id): ?Pattern
    {
        return Pattern::query()
            ->where('id', $id)
            ->select([
                'id',
                'title',
            ])
            ->first();
    }

    protected function getSources(): array
    {
        return DB::table($this->newPatternVideo->getTable())
            ->distinct()
            ->pluck('source')
            ->toArray();
    }
}
