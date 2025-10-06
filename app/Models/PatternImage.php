<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * @property-read int $id
 *
 * @property string $path
 * @property string $extension
 * @property int $size
 * @property string $mime_type
 * @property string $hash_algorithm
 * @property string $hash
 * @property int $pattern_id
 *
 * @property-read \Carbon\Carbon $created_at
 * @property-read \Carbon\Carbon $updated_at
 */
class PatternImage extends Model
{
    protected $fillable = [
        'path',
        'extension',
        'size',
        'mime_type',
        'hash_algorithm',
        'hash',
        'pattern_id',
    ];

    public function pattern(): BelongsTo
    {
        return $this->belongsTo(Pattern::class);
    }
}
