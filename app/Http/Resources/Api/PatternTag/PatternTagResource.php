<?php

declare(strict_types=1);

namespace App\Http\Resources\Api\PatternTag;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class PatternTagResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->resource->id,
            'name' => $this->resource->name,
        ];
    }

    public function jsonOptions()
    {
        return JSON_UNESCAPED_UNICODE;
    }
}
