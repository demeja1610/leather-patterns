<?php

namespace App\Dto\Parser\Pattern;

use App\Dto\Dto;
use Carbon\Carbon;

class ReviewDto extends Dto
{
    public function __construct(
        protected readonly string $reviewerName,
        protected readonly Carbon $reviewedAt,
        protected readonly ?string $comment = null,
        protected readonly float $rating = 0.0,
    ) {}

    public static function fromArray(array $data): self
    {
        return new self(
            reviewerName: $data['reviewer_name'],
            reviewedAt: Carbon::parse($data['reviewed_at']),
            comment: $data['comment'],
            rating: $data['rating'],
        );
    }

    public function toArray(): array
    {
        return [
            'reviewer_name' => $this->reviewerName,
            'reviewed_at' => $this->reviewedAt->toDateTimeString(),
            'comment' => $this->comment,
            'rating' => $this->rating,
        ];
    }

    public function getReviewerName(): string
    {
        return $this->reviewerName;
    }

    public function getReviewedAt(): Carbon
    {
        return $this->reviewedAt;
    }

    public function getComment(): ?string
    {
        return $this->comment;
    }

    public function getRating(): float
    {
        return $this->rating;
    }
}
