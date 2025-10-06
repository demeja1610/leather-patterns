<?php

declare(strict_types=1);

namespace App\Models;

use App\Enum\VideoSourceEnum;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * @property-read int $id
 *
 * @property string $url
 * @property string $source
 * @property null|string $source_identifier
 *
 * @property-read \Carbon\Carbon $created_at
 * @property-read \Carbon\Carbon $updated_at
 */
class PatternVideo extends Model
{
    protected $fillable = [
        'url',
        'source',
        'source_identifier',
    ];

    protected $casts = [
        'source' => VideoSourceEnum::class,
    ];

    public function pattern(): BelongsTo
    {
        return $this->belongsTo(Pattern::class);
    }

    public function embedUrl(): Attribute
    {
        return new Attribute(
            get: function ($value, array $attributes) {
                return match ($attributes['source']) {
                    VideoSourceEnum::YOUTUBE->value => "https://www.youtube.com/embed/{$attributes['source_identifier']}",
                    VideoSourceEnum::VK->value => $this->getVkEmbedUrl($attributes),
                    default => null,
                };
            }
        );
    }

    protected function getVkEmbedUrl(array $attributes): string
    {
        $id = explode('_', $attributes['source_identifier']);

        return "https://vkvideo.ru/video_ext.php?oid={$id[0]}&id={$id[1]}";
    }
}
