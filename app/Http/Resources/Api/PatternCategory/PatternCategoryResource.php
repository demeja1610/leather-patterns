<?php

declare(strict_types=1);

namespace App\Http\Resources\Api\PatternCategory;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class PatternCategoryResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
        ];
    }

    public function jsonOptions()
    {
        return JSON_UNESCAPED_UNICODE;
    }
}
