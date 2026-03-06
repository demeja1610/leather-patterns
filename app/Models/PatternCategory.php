<?php

declare(strict_types=1);

namespace App\Models;

use App\Models\PatternTag;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

/**
 * @property-read int $id
 *
 * @property string $name
 * @property null|int $replace_id
 * @property null|int $replace_tag_id
 * @property bool $remove_on_appear
 * @property bool $is_published
 *
 * @property-read \Carbon\Carbon $created_at
 * @property-read \Carbon\Carbon $updated_at
 */
class PatternCategory extends Model
{
    protected $fillable = [
        'name',
        'replace_id',
        'replace_tag_id',
        'remove_on_appear',
        'is_published',
    ];

    public function patterns(): BelongsToMany
    {
        return $this->belongsToMany(related: Pattern::class);
    }

    public function replacement(): HasOne
    {
        return $this->hasOne(
            related: static::class,
            foreignKey: 'id',
            localKey: 'replace_id',
        );
    }

    public function tagReplacement(): HasOne
    {
        return $this->hasOne(
            related: PatternTag::class,
            foreignKey: 'id',
            localKey: 'replace_tag_id',
        );
    }

    public function replacementFor(): HasMany
    {
        return $this->hasMany(
            related: static::class,
            foreignKey: 'replace_id',
            localKey: 'id',
        );
    }

    public function replacementForTags(): HasMany
    {
        return $this->hasMany(
            related: PatternTag::class,
            foreignKey: 'replace_category_id',
            localKey: 'id',
        );
    }

    public function isDeletable(): bool
    {
        if ($this->patterns_count === null) {
            $this->loadCount(relations: 'patterns');
        }

        if ($this->replacement_for_count === null) {
            $this->loadCount(relations: 'replacementFor');
        }

        if ($this->replacement_for_tags_count === null) {
            $this->loadCount(relations: 'replacementForTags');
        }

        return $this->remove_on_appear === false
            && $this->replace_id === null
            && $this->replace_tag_id === null
            && $this->patterns_count === 0
            && $this->replacement_for_count === 0
            && $this->replacement_for_tags_count === 0;
    }

    protected function casts(): array
    {
        return [
            'remove_on_appear' => 'boolean',
            'is_published' => 'boolean',
        ];
    }
}
