<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

/**
 * @property-read int $id
 *
 * @property string $name
 *
 * @property-read \Carbon\Carbon $created_at
 * @property-read \Carbon\Carbon $updated_at
 */
class PatternCategory extends Model
{
    protected $fillable = [
        'name',
    ];

    public function patterns(): BelongsToMany
    {
        return $this->belongsToMany(Pattern::class);
    }
}
