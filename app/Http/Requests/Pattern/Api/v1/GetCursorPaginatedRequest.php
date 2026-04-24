<?php

declare(strict_types=1);

namespace App\Http\Requests\Pattern\Api\v1;

use App\Enum\PatternOrderEnum;
use Illuminate\Validation\Rule;
use Illuminate\Foundation\Http\FormRequest;

class GetCursorPaginatedRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            's' => [
                'nullable',
                'string',
                'max:255',
            ],
            'category' => [
                'nullable',
                'array',
            ],
            'category.*' => [
                'integer',
            ],
            'tag' => [
                'nullable',
                'array',
            ],
            'tag.*' => [
                'integer',
            ],
            'has_author' => [
                'nullable',
                'integer',
            ],
            'author' => [
                'nullable',
                'array',
            ],
            'author.*' => [
                'integer',
            ],
            'has_video' => [
                'nullable',
                'integer',
            ],
            'has_review' => [
                'nullable',
                'integer',
            ],
            'order' => [
                'nullable',
                Rule::enum(PatternOrderEnum::class),
            ],
            'cursor' => [
                'nullable',
                'string',
            ],
        ];
    }
}
