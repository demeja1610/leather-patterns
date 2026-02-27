<?php

declare(strict_types=1);

namespace App\Http\Requests\Admin\PatternTag\Api\v1;

use Illuminate\Foundation\Http\FormRequest;

class SearchRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'q' => [
                'required',
                'string',
            ],

            'except_id' => [
                'nullable',
                'numeric',
            ],

            'pattern_replaceable' => [
                'nullable',
                'numeric',
            ],

            'pattern_removable' => [
                'nullable',
                'numeric',
            ],
        ];
    }
}
