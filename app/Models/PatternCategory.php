<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * @property-read int $id
 *
 * @property string $name
 * @property null|int $replace_id
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
        'remove_on_appear',
        'is_published',
    ];

    protected $casts = [
        'remove_on_appear' => 'boolean',
        'is_published' => 'boolean',
    ];

    public function patterns(): BelongsToMany
    {
        return $this->belongsToMany(Pattern::class);
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
}
