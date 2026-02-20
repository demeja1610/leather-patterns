<?php

declare(strict_types=1);

namespace App\Http\Requests\Admin\PatternTag;

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
                'min:1',
                'unique:pattern_tags,name',
            ],
            'replace_id' => [
                'nullable',
                'numeric',
                'exists:pattern_tags,id',
            ],
            'replace_author_id' => [
                'nullable',
                'numeric',
                'exists:pattern_authors,id',
            ],
            'replace_category_id' => [
                'nullable',
                'numeric',
                'exists:pattern_categories,id',
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
