<?php

declare(strict_types=1);

namespace App\Http\Requests\Admin\PatternCategory;

use Illuminate\Foundation\Http\FormRequest;

class EditRequest extends FormRequest
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
                "unique:pattern_categories,name,{$this->id}",
            ],
            'replace_id' => [
                'nullable',
                'numeric',
                'exists:pattern_categories,id'
            ],
            'remove_on_appear' => [
                'nullable',
                'in:on'
            ],
            'is_published' => [
                'nullable',
                'in:on'
            ],
        ];
    }
}
