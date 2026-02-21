<?php

declare(strict_types=1);

namespace App\Http\Requests\Admin\PatternAuthor;

use Illuminate\Foundation\Http\FormRequest;

class CreateRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'name' => [
                'required',
                'string',
                'max:255',
                'min:2',
                'unique:pattern_authors,name',
            ],
            'replace_id' => [
                'nullable',
                'numeric',
                'exists:pattern_authors,id',
            ],
            'remove_on_appear' => [
                'nullable',
                'in:on',
            ],
            'is_published' => [
                'nullable',
                'in:on',
            ],
        ];
    }
}
