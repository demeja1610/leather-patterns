<?php

declare(strict_types=1);

namespace App\Http\Resources\Api\v1\App\Pattern;

use Illuminate\Http\Request;
use App\Models\PatternAuthor;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Http\Resources\Json\JsonResource;
use App\Http\Resources\Api\v1\App\PatternTag\PatternTagResource;
use App\Http\Resources\Api\v1\App\PatternAuthor\PatternAuthorResource;
use App\Http\Resources\Api\v1\App\PatternCategory\PatternCategoryResource;
use App\Http\Resources\Api\v1\App\PatternFile\PatternFileResource;
use App\Http\Resources\Api\v1\App\PatternImage\PatternImageResource;

class PatternResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'title' => $this->title,
            'avg_rating' => (int) $this->avg_rating,
            'reviews_count' => $this->whenCounted('reviews', $this->reviews_count),
            'created_at' => $this->created_at->format('d.m.Y H:i'),
            'categories' => $this->whenLoaded('categories', fn(Collection $categories) => PatternCategoryResource::collection($categories)),
            'tags' => $this->whenLoaded('tags', fn(Collection $tags) => PatternTagResource::collection($tags)),
            'author' => $this->whenLoaded('author', fn(PatternAuthor $author) => PatternAuthorResource::make($author)),
            'images' => $this->whenLoaded('images', fn(Collection $images) => PatternImageResource::collection($images)),
            'files' => $this->whenLoaded('files', fn(Collection $files) => PatternFileResource::collection($files)),
        ];
    }

    public function jsonOptions()
    {
        return JSON_UNESCAPED_UNICODE;
    }
}
