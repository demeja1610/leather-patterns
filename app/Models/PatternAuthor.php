<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class PatternAuthor extends Model
{
    protected $fillable = [
        'name',
    ];

    public function patterns(): HasMany
    {
        return $this->hasMany(related: Pattern::class, foreignKey: 'author_id');
    }

    public function replacementForTags(): HasMany
    {
        return $this->hasMany(
            related: PatternTag::class,
            foreignKey: 'replace_author_id',
            localKey: 'id',
        );
    }
}
