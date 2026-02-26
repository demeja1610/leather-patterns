<?php

declare(strict_types=1);

namespace App\Http\Controllers\Admin\Pattern\Page;

use Carbon\Carbon;
use App\Models\Pattern;
use App\Models\PatternTag;
use App\Models\PatternAuthor;
use App\Enum\PatternSourceEnum;
use App\Models\PatternCategory;
use Illuminate\Contracts\View\View;
use App\Http\Controllers\Controller;
use Illuminate\Database\Eloquent\Builder;
use App\Http\Requests\Admin\Pattern\ListRequest;
use Illuminate\Contracts\Pagination\CursorPaginator;

class ListPageController extends Controller
{
    protected array $activeFilters = [];
    protected array $extraData = [];

    public function __invoke(ListRequest $request): View
    {
        $patterns = $this->getPatterns(request: $request);

        return view(view: 'pages.admin.pattern.list', data: [
            'activeFilters' => $this->activeFilters,
            'extraData' => $this->extraData,
            'patterns' => $patterns,
        ]);
    }

    protected function getPatterns(ListRequest &$request): CursorPaginator
    {
        $cursor = $request->input(key: 'cursor');

        $q = Pattern::query();

        $this->applyFilters(
            request: $request,
            query: $q,
        );

        $q->withCount(relations: [
            'reviews',
            'videos',
            'files',
        ]);

        $q->with(relations: [
            'images',
            'author',
            'categories',
            'tags',
            'meta',
        ]);

        return $q->orderBy('id', 'desc')->cursorPaginate(
            perPage: 30,
            cursor: $cursor,
        )->withQueryString();
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

    protected function applyFilters(ListRequest &$request, Builder &$query): void
    {
        $id = $request->input(key: 'id');

        if ($id !== null) {
            $this->activeFilters['id'] = $id;

            $query->where('id', $id);
        }

        $sourceStr = $request->input('source');

        if ($sourceStr !== null) {
            $source = PatternSourceEnum::tryFrom($sourceStr);

            if ($source !== null) {
                $query->where('source', $source->value);
            }
        }

        $title = $request->input(key: 'title');

        if ($title !== null) {
            $this->activeFilters['title'] = $title;

            $query->where('title', 'LIKE', "%{$title}%");
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

        $hasImages = $request->input(key: 'has_image');

        if ($hasImages !== null) {
            $this->activeFilters['has_image'] = (bool) $hasImages;

            if ((bool) $hasImages) {
                $query->whereHas(relation: 'images');
            } else {
                $query->whereDoesntHave(relation: 'images');
            }
        }

        $hasCategories = $request->input(key: 'has_categories');
        $categoryId = $request->input('category_id');

        if ($hasCategories !== null || $categoryId !== null) {
            if ($hasCategories !== null) {
                $this->activeFilters['has_categories'] = (bool) $hasCategories;
            }

            if ($categoryId !== null) {
                $this->activeFilters['category_id'] = $categoryId;
                $this->extraData['selected_category'] = $this->getCategory(id: $categoryId);
            }

            if ((bool) $hasCategories === true || $categoryId !== null) {
                $query->whereHas(relation: 'categories', callback: function (Builder $sq) use (&$categoryId) {
                    if ($categoryId !== null) {
                        $sq->where('pattern_category_id', $categoryId);
                    }

                    return $sq;
                });
            } else {
                $query->whereDoesntHave(relation: 'categories');
            }
        }

        $hasTags = $request->input(key: 'has_tags');
        $tagId = $request->input('tag_id');

        if ($hasTags !== null || $tagId !== null) {
            if ($hasTags !== null) {
                $this->activeFilters['has_tags'] = (bool) $hasTags;
            }

            if ($tagId !== null) {
                $this->activeFilters['tag_id'] = $tagId;
                $this->extraData['selected_tag'] =  $this->getTag(id: $tagId);
            }

            if ((bool) $hasTags === true || $tagId !== null) {
                $query->whereHas(relation: 'tags', callback: function (Builder $sq) use (&$tagId) {
                    if ($tagId !== null) {
                        $sq->where('pattern_tag_id', $tagId);
                    }

                    return $sq;
                });
            } else {
                $query->whereDoesntHave(relation: 'tags');
            }
        }

        $hasAuthor = $request->input(key: 'has_author');
        $authorId = $request->input('author_id');

        if ($hasAuthor !== null || $authorId !== null) {
            if ($hasAuthor !== null) {
                $this->activeFilters['has_author'] = (bool) $hasAuthor;
            }

            if ($authorId !== null) {
                $this->activeFilters['author_id'] = $authorId;
                $this->extraData['selected_author'] =  $this->getAuthor(id: $authorId);
            }

            if ((bool) $hasAuthor === true || $authorId !== null) {
                if ($authorId === null) {
                    $query->whereNotNull('author_id');
                } else {
                    $query->where('author_id', $authorId);
                }
            } else {
                $query->whereNull('author_id');
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

        $hasFiles = $request->input(key: 'has_files');

        if ($hasFiles !== null) {
            $this->activeFilters['has_files'] = (bool) $hasFiles;

            if ((bool) $hasFiles) {
                $query->whereHas(relation: 'files');
            } else {
                $query->whereDoesntHave(relation: 'files');
            }
        }

        $hasVideos = $request->input(key: 'has_videos');

        if ($hasVideos !== null) {
            $this->activeFilters['has_videos'] = (bool) $hasVideos;

            if ((bool) $hasVideos) {
                $query->whereHas(relation: 'videos');
            } else {
                $query->whereDoesntHave(relation: 'videos');
            }
        }

        $hasReviews = $request->input(key: 'has_reviews');

        if ($hasReviews !== null) {
            $this->activeFilters['has_reviews'] = (bool) $hasReviews;

            if ((bool) $hasReviews) {
                $query->whereHas(relation: 'reviews');
            } else {
                $query->whereDoesntHave(relation: 'reviews');
            }
        }

        $patternDownloaded = $request->input(key: 'pattern_downloaded');

        if ($patternDownloaded !== null) {
            $this->activeFilters['pattern_downloaded'] = (bool) $patternDownloaded;

            $query->whereHas(
                relation: 'meta',
                callback: fn(Builder $sq) => $sq
                    ->where('pattern_downloaded', (bool) $patternDownloaded)
            );
        }

        $imagesDownloaded = $request->input(key: 'images_downloaded');

        if ($imagesDownloaded !== null) {
            $this->activeFilters['images_downloaded'] = (bool) $imagesDownloaded;

            $query->whereHas(
                relation: 'meta',
                callback: fn(Builder $sq) => $sq
                    ->where('images_downloaded', (bool) $imagesDownloaded)
            );
        }

        $downloadUrlWrong = $request->input(key: 'is_download_url_wrong');

        if ($downloadUrlWrong !== null) {
            $this->activeFilters['is_download_url_wrong'] = (bool) $downloadUrlWrong;

            $query->whereHas(
                relation: 'meta',
                callback: fn(Builder $sq) => $sq
                    ->where('is_download_url_wrong', (bool) $downloadUrlWrong)
            );
        }

        $videoChecked = $request->input(key: 'is_video_checked');

        if ($videoChecked !== null) {
            $this->activeFilters['is_video_checked'] = (bool) $videoChecked;

            $query->whereHas(
                relation: 'meta',
                callback: fn(Builder $sq) => $sq
                    ->where('is_video_checked', (bool) $videoChecked)
            );
        }
    }
}
