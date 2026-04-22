<?php

namespace App\Models;

use App\Models\User;
use App\Models\Pattern;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * @property-read int $id
 *
 * @property int $pattern_id
 * @property int $user_id
 *
 * @property-read \Carbon\Carbon $created_at
 * @property-read \Carbon\Carbon $updated_at
 */
class PatternLike extends Model
{
    protected $fillable = [
        'pattern_id',
        'user_id',
    ];

    public function pattern(): BelongsTo
    {
        return $this->belongsTo(Pattern::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
