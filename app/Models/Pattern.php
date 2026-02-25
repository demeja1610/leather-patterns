<?php

declare(strict_types=1);

namespace App\Models;

use App\Enum\PatternSourceEnum;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

/**
 * @property-read int $id
 *
 * @property int $author_id
 * @property float $avg_rating
 * @property null|string $title
 * @property PatternSourceEnum $source
 * @property string $source_url
 * @property boolean $is_published
 *
 * @property-read \Carbon\Carbon $created_at
 * @property-read \Carbon\Carbon $updated_at
 */
class Pattern extends Model
{
    protected $fillable = [
        'author_id',
        'avg_rating',
        'title',
        'source',
        'source_url',
        'is_published',
    ];

    public function images(): HasMany
    {
        return $this->hasMany(related: PatternImage::class);
    }

    public function categories(): BelongsToMany
    {
        return $this->belongsToMany(related: PatternCategory::class);
    }

    public function tags(): BelongsToMany
    {
        return $this->belongsToMany(related: PatternTag::class);
    }

    public function videos(): HasMany
    {
        return $this->hasMany(related: PatternVideo::class);
    }

    public function reviews(): HasMany
    {
        return $this->hasMany(related: PatternReview::class);
    }

    public function author(): BelongsTo
    {
        return $this->belongsTo(related: PatternAuthor::class);
    }

    public function files(): HasMany
    {
        return $this->hasMany(related: PatternFile::class);
    }

    public function meta(): HasOne
    {
        return $this->hasOne(related: PatternMeta::class);
    }

    public function isDeletable(): bool
    {
        return true;
    }

    protected function casts(): array
    {
        return [
            'source' => PatternSourceEnum::class,
            'is_published' => 'boolean',
        ];
    }
}
