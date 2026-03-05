<?php

namespace App\Models;

use App\Enum\SocialTypeEnum;
use App\Models\PatternAuthor;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * @property-read int $id
 *
 * @property \App\Enum\SocialTypeEnum $type
 * @property string $url
 * @property int $author_id
 * @property bool $is_published
 *
 * @property-read \Carbon\Carbon $created_at
 * @property-read \Carbon\Carbon $updated_at
 */
class PatternAuthorSocial extends Model
{
    protected $fillable = [
        'type',
        'url',
        'author_id',
        'is_published',
    ];

    public function author(): BelongsTo
    {
        return $this->belongsTo(PatternAuthor::class, 'author_id');
    }

    public function isDeletable(): bool
    {
        return true;
    }

    protected function casts(): array
    {
        return [
            'type' => SocialTypeEnum::class,
        ];
    }
}
