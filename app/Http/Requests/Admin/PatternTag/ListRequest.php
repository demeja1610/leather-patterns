<?php

namespace App\Http\Requests\Admin\PatternTag;

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
            'id' => 'nullable|numeric',
            'name' => 'nullable|string',
            'created_at' => 'nullable|date',
        ];
    }
}
