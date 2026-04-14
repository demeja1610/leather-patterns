<?php

declare(strict_types=1);

namespace App\Http\Controllers\Admin\PatternAuthorSocial\Page;

use Carbon\Carbon;
use App\Enum\SocialTypeEnum;
use App\Models\PatternAuthor;
use App\Models\PatternAuthorSocial;
use Illuminate\Contracts\View\View;
use App\Http\Controllers\Controller;
use Illuminate\Database\Eloquent\Builder;
use App\Http\Requests\Admin\PatternAuthorSocial\ListRequest;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

class ListPageController extends Controller
{
    protected array $activeFilters = [];
    protected array $extraData = [];

    public function __invoke(ListRequest $request): View
    {
        $socials = $this->getSocials(request: $request);
        $types = SocialTypeEnum::cases();

        return view(view: 'pages.admin.pattern-author-social.list', data: [
            'activeFilters' => $this->activeFilters,
            'extraData' => $this->extraData,
            'socials' => $socials,
            'types' => $types,
        ]);
    }

    protected function getSocials(ListRequest &$request): LengthAwarePaginator
    {
        $page = $request->input(key: 'page');

        $q = PatternAuthorSocial::query();

        $this->applyFilters(
            request: $request,
            query: $q,
        );

        $q->with(relations: 'author');

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

        $typeStr = $request->input('type');

        if ($typeStr !== null) {
            $type = SocialTypeEnum::tryFrom($typeStr);

            if ($type !== null) {
                $this->activeFilters['type'] = $type;

                $query->where('type', $type->value);
            }
        }

        $authorId = $request->input('author_id');

        if ($authorId !== null) {
            $this->activeFilters['author_id'] = $authorId;

            $this->extraData['selected_author'] =  $this->getAuthor(id: $authorId);

            $query->where('author_id', $authorId);
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

        $isPublished = $request->input(key: 'is_published');

        if ($isPublished !== null) {
            $this->activeFilters['is_published'] = (bool) $isPublished;

            if ((bool) $isPublished) {
                $query->where('is_published', true);
            } else {
                $query->where('is_published', false);
            }
        }
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
