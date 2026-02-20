<?php

declare(strict_types=1);

namespace App\Http\Requests\PatternAuthor\Api\v1;

use Illuminate\Foundation\Http\FormRequest;

class GetAllRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'from' => [
                'nullable',
                'numeric'
            ],
        ];
    }
}
