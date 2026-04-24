<?php

declare(strict_types=1);

namespace App\Http\Resources\Api\v1\App\PatternFile;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Facades\Storage;

class PatternFileResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'url' => Storage::disk($this->getSaveToDiskName())->url($this->path),
        ];
    }
}
