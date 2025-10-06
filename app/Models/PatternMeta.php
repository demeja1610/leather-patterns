<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PatternMeta extends Model
{
    protected $fillable = [
        'pattern_downloaded',
        'images_downloaded',
        'reviews_updated_at',
        'pattern_id',
        'is_download_url_wrong',
        'is_video_checked',
    ];

    public function pattern(): BelongsTo
    {
        return $this->belongsTo(Pattern::class);
    }
}
