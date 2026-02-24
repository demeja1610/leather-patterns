<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * @property-read int $id
 *
 * @property string $reviewer_name
 * @property int $rating
 * @property string $comment
 * @property bool $is_approved
 * @property null|int $user_id
 * @property int $pattern_id
 *
 * @property-read \Carbon\Carbon $created_at
 * @property-read \Carbon\Carbon $updated_at
 */
class PatternReview extends Model
{
    protected $fillable = [
        'reviewer_name',
        'rating',
        'comment',
        'is_approved',
        'user_id',
        'pattern_id',
    ];

    public function pattern(): BelongsTo
    {
        return $this->belongsTo(related: Pattern::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(related: User::class);
    }

    public function isDeletable(): bool
    {
        return true;
    }
}
