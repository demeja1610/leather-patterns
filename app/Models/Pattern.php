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
 * @property int $author_id
 *
 * @property null|string $title
 * @property PatternSourceEnum $source
 * @property string $source_url
 *
 * @property-read \Carbon\Carbon $created_at
 * @property-read \Carbon\Carbon $updated_at
 */
class Pattern extends Model
{
    protected $fillable = [
        'author_id',
        'title',
        'source',
        'source_url',
    ];

    protected $casts = [
        'source' => PatternSourceEnum::class,
    ];

    public function images(): HasMany
    {
        return $this->hasMany(PatternImage::class);
    }

    public function categories(): BelongsToMany
    {
        return $this->belongsToMany(PatternCategory::class);
    }

    public function tags(): BelongsToMany
    {
        return $this->belongsToMany(PatternTag::class);
    }

    public function videos(): HasMany
    {
        return $this->hasMany(PatternVideo::class);
    }

    public function reviews(): HasMany
    {
        return $this->hasMany(PatternReview::class);
    }

    public function author(): BelongsTo
    {
        return $this->belongsTo(PatternAuthor::class);
    }

    public function files(): HasMany
    {
        return $this->hasMany(PatternFile::class);
    }

    public function meta(): HasOne
    {
        return $this->hasOne(PatternMeta::class);
    }
}
