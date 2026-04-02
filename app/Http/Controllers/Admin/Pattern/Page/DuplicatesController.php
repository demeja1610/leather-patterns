<?php

declare(strict_types=1);

namespace App\Http\Controllers\Admin\Pattern\Page;

use App\Models\PatternFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Contracts\View\View;
use App\Http\Controllers\Controller;
use Illuminate\Database\Query\Builder;
use App\Http\Requests\Admin\Pattern\ListRequest;
use App\Dto\PatternFile\DuplicatedPatternFileDto;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

class DuplicatesController extends Controller
{
    protected array $activeFilters = [];
    protected array $extraData = [];

    public function __invoke(ListRequest $request): View
    {
        $duplicates = $this->getDuplicated(request: $request);

        $this->mutateDuplicates($duplicates);

        return view(view: 'pages.admin.pattern.duplicates', data: [
            'activeFilters' => $this->activeFilters,
            'extraData' => $this->extraData,
            'duplicates' => $duplicates,
        ]);
    }

    protected function getDuplicated(ListRequest &$request): LengthAwarePaginator
    {
        $page = $request->input('page');

        $model = new PatternFile();

        $q = DB::table($model->getTable())
            ->groupBy('hash')
            ->select([
                'hash',
                DB::raw('COUNT(hash) as duplicates_count'),
                DB::raw('GROUP_CONCAT(DISTINCT pattern_id) as patterns_ids'),
            ]);

        $this->applyFilters(
            request: $request,
            query: $q,
        );

        return $q->paginate(
            perPage: 30,
            page: $page,
        )->withQueryString();
    }

    protected function mutateDuplicates(LengthAwarePaginator &$duplicates): void
    {
        $duplicates->through(function (object $duplicate) {
            return new DuplicatedPatternFileDto(
                hash: $duplicate->hash,
                duplicatesCount: $duplicate->duplicates_count,
                patternIds: explode(',', $duplicate->patterns_ids),
            );
        });
    }

    protected function applyFilters(ListRequest &$request, Builder &$query): void
    {
        $duplicatesCount = $request->input('duplicates_count');

        if ($duplicatesCount !== null) {
            $this->activeFilters['duplicates_count'] = $duplicatesCount;

            $query->having('duplicates_count', '=', $duplicatesCount);
        } else {
            $query->having('duplicates_count', '>', 1);
        }
    }
}
