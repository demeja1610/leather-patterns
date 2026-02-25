<?php

declare(strict_types=1);

namespace App\Http\Requests\Admin\PatternReview;

use Illuminate\Foundation\Http\FormRequest;

class ListRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'id' => [
                'nullable',
                'numeric',
            ],
            'created_at' => [
                'nullable',
                'date',
            ],
            'pattern_id' => [
                'nullable',
                'numeric',
            ]
        ];
    }
}
