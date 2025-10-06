<?php

namespace App\Models;

use App\Enum\FileTypeEnum;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * @property int $id
 *
 * @property string $path
 * @property string $type
 * @property string $extension
 * @property int $size
 * @property string $mime_type
 * @property string $hash_algorithm
 * @property string $hash
 * @property int $pattern_id
 *
 * @property \Carbon\Carbon $created_at
 * @property \Carbon\Carbon $updated_at
 */
class PatternFile extends Model
{
    protected $fillable = [
        'path',
        'type',
        'extension',
        'size',
        'mime_type',
        'hash_algorithm',
        'hash',
        'pattern_id',
    ];

    protected $casts = [
        'type' => FileTypeEnum::class,
    ];

    public function pattern(): BelongsTo
    {
        return $this->belongsTo(Pattern::class);
    }
}
