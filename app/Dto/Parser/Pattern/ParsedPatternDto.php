<?php

namespace App\Dto\Parser\Pattern;

use App\Dto\Dto;
use App\Models\Pattern;

class ParsedPatternDto extends Dto
{
    public function __construct(
        protected readonly Pattern $pattern,
        protected readonly ?string $title,
        protected readonly CategoryListDto $categories,
        protected readonly TagListDto $tags,
        protected readonly ImageListDto $images,
        protected readonly FileListDto $files,
        protected readonly VideoListDto $videos,
        protected readonly ReviewListDto $reviews,
    ) {}

    public static function fromArray(array $data): self
    {
        return new self(
            pattern: new Pattern($data['pattern']),
            title: $data['title'],
            categories: CategoryListDto::fromArray($data['categories']),
            tags: TagListDto::fromArray($data['tags']),
            images: ImageListDto::fromArray($data['images']),
            files: FileListDto::fromArray($data['files']),
            videos: VideoListDto::fromArray($data['videos']),
            reviews: ReviewListDto::fromArray($data['reviews']),
        );
    }

    public function toArray(): array
    {
        return [
            'pattern' => $this->pattern->toArray(),
            'title' => $this->title,
            'categories' => $this->categories->toArray(),
            'tags' => $this->tags->toArray(),
            'images' => $this->images->toArray(),
            'files' => $this->files->toArray(),
            'videos' => $this->videos->toArray(),
            'reviews' => $this->reviews->toArray(),
        ];
    }

    public function getPattern(): Pattern
    {
        return $this->pattern;
    }

    public function getTitle(): ?string
    {
        return $this->title;
    }

    public function getCategories(): CategoryListDto
    {
        return $this->categories;
    }

    public function getTags(): TagListDto
    {
        return $this->tags;
    }

    public function getImages(): ImageListDto
    {
        return $this->images;
    }

    public function getFiles(): FileListDto
    {
        return $this->files;
    }

    public function getVideos(): VideoListDto
    {
        return $this->videos;
    }

    public function getReviews(): ReviewListDto
    {
        return $this->reviews;
    }
}
