<?php

declare(strict_types=1);

namespace App\Http\Controllers\Admin\PatternFile\Page;

use Carbon\Carbon;
use App\Models\Pattern;
use App\Models\PatternFile;
use App\Enum\OrderDirectionEnum;
use Illuminate\Support\Facades\DB;
use App\Enum\OrderablePropertyEnum;
use Illuminate\Contracts\View\View;
use App\Http\Controllers\Controller;
use Illuminate\Database\Eloquent\Builder;
use App\Http\Requests\Admin\Pattern\ListRequest;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

class ListPageController extends Controller
{
    protected array $activeFilters = [];
    protected array $extraData = [];

    private PatternFile $newFile;

    public function __construct()
    {
        $this->newFile = new PatternFile();
    }

    public function __invoke(ListRequest $request): View
    {
        $files = $this->getFiles(request: $request);

        $types = $this->getTypes();
        $exts = $this->getExtensions();
        $mimeTypes = $this->getMimeTypes();
        $hashAlgos = $this->getHashAlgos();
        $orders = array_keys($this->getOrderableProps());
        $orderDirections = array_map(
            array: OrderDirectionEnum::cases(),
            callback: fn(OrderDirectionEnum $orderDirection) => $orderDirection->value,
        );

        return view(view: 'pages.admin.pattern-file.list', data: [
            'activeFilters' => $this->activeFilters,
            'extraData' => $this->extraData,
            'files' => $files,
            'types' => $types,
            'exts' => $exts,
            'mimeTypes' => $mimeTypes,
            'hashAlgos' => $hashAlgos,
            'orders' => $orders,
            'orderDirections' => $orderDirections,
        ]);
    }

    protected function getFiles(ListRequest &$request): LengthAwarePaginator
    {
        $page = $request->input(key: 'page');
        $orderDirection = $this->getOrderDirection($request);

        $q = PatternFile::query();

        $this->applyFilters(
            request: $request,
            query: $q,
            orderDirection: $orderDirection,
        );

        $q->with(relations: [
            'pattern.images'
        ]);

        $q->orderBy('id', $orderDirection->value);

        return $q->paginate(
            perPage: 30,
            page: $page,
        )->withQueryString();
    }

    protected function applyFilters(ListRequest &$request, Builder &$query, OrderDirectionEnum $orderDirection): void
    {
        $id = $request->input(key: 'id');

        if ($id !== null) {
            $this->activeFilters['id'] = $id;

            $query->where('id', $id);
        }

        $hash = $request->input(key: 'hash');

        if ($hash !== null) {
            $this->activeFilters['hash'] = $hash;

            $query->where('hash', $hash);
        }

        $type = $request->input(key: 'type');

        if ($type !== null) {
            $this->activeFilters['type'] = $type;

            $query->where('type', $type);
        }

        $ext = $request->input(key: 'ext');

        if ($ext !== null) {
            $this->activeFilters['ext'] = $ext;

            $query->where('extension', $ext);
        }

        $mimeType = $request->input(key: 'mime_type');

        if ($mimeType !== null) {
            $this->activeFilters['mime_type'] = $mimeType;

            $query->where('mime_type', $mimeType);
        }

        $hashAlgo = $request->input(key: 'hash_algo');

        if ($hashAlgo !== null) {
            $this->activeFilters['hash_algo'] = $hashAlgo;

            $query->where('hash_algorithm', $hashAlgo);
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

        $patternId = $request->input('pattern_id');

        if ($patternId !== null) {
            $this->activeFilters['pattern_id'] = $patternId;
            $this->extraData['selected_pattern'] = $this->getPattern(id: $patternId);

            $query->where('pattern_id', $patternId);
        }

        $orderByStr = $request->input('order_by');

        if ($orderByStr !== null) {
            $orderBy = OrderablePropertyEnum::tryFrom($orderByStr);

            if ($orderBy !== null && $orderBy !== OrderablePropertyEnum::ID && array_key_exists($orderByStr, $this->getOrderableProps())) {
                $this->activeFilters['order_by'] = $orderByStr;

                $query->orderBy($this->getOrderableProps()[$orderByStr], $orderDirection->value);
            }
        }

        $this->activeFilters['order_direction'] = $orderDirection->value;
    }

    protected function getPattern(int|string &$id): ?Pattern
    {
        return Pattern::find($id);
    }

    protected function getTypes(): array
    {
        return DB::table($this->newFile->getTable())
            ->distinct()
            ->pluck('type')
            ->toArray();
    }

    protected function getExtensions(): array
    {
        return DB::table($this->newFile->getTable())
            ->distinct()
            ->pluck('extension')
            ->toArray();
    }

    protected function getMimeTypes(): array
    {
        return DB::table($this->newFile->getTable())
            ->distinct()
            ->pluck('mime_type')
            ->toArray();
    }

    protected function getHashAlgos(): array
    {
        return DB::table($this->newFile->getTable())
            ->distinct()
            ->pluck('hash_algorithm')
            ->toArray();
    }

    /**
     * @return array<\App\Enum\OrderablePropertyEnum>
     */
    protected function getOrderableProps(): array
    {
        return [
            OrderablePropertyEnum::ID->value => 'id',
            OrderablePropertyEnum::SIZE->value => 'size',
            OrderablePropertyEnum::CREATED_AT->value => 'created_at',
            OrderablePropertyEnum::PATTERN_ID->value => 'pattern_id',
        ];
    }

    protected function getOrderDirection(ListRequest &$request): OrderDirectionEnum
    {
        $orderDirectionStr = $request->input('order_direction');
        $orderDirection = null;

        if ($orderDirectionStr !== null) {
            $orderDirection = OrderDirectionEnum::tryFrom($orderDirectionStr);
        }

        return $orderDirection === null ? OrderDirectionEnum::DESC : $orderDirection;
    }
}
