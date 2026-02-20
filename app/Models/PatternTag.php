<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

/**
 * @property-read int $id
 *
 * @property string $name
 * @property null|int $replace_id
 * @property null|int $replace_author_id
 * @property null|int $replace_category_id
 * @property bool $remove_on_appear
 * @property bool $is_published
 *
 * @property-read \Carbon\Carbon $created_at
 * @property-read \Carbon\Carbon $updated_at
 */
class PatternTag extends Model
{
    protected $fillable = [
        'name',
        'replace_id',
        'replace_author_id',
        'replace_category_id',
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

    public function replacementFor(): HasMany
    {
        return $this->hasMany(
            related: static::class,
            foreignKey: 'replace_id',
            localKey: 'id',
        );
    }

    public function authorReplacement(): HasOne
    {
        return $this->hasOne(
            related: PatternAuthor::class,
            foreignKey: 'id',
            localKey: 'replace_author_id',
        );
    }

    public function categoryReplacement(): HasOne
    {
        return $this->hasOne(
            related: PatternCategory::class,
            foreignKey: 'id',
            localKey: 'replace_category_id',
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

        return $this->remove_on_appear === false
            && $this->replace_id === null
            && $this->replace_author_id === null
            && $this->replace_category_id === null
            && $this->patterns_count === 0
            && $this->replacement_for_count === 0;
    }

    protected function casts(): array
    {
        return [
            'remove_on_appear' => 'boolean',
            'is_published' => 'boolean',
        ];
    }
}
