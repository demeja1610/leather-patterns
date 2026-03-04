<?php

namespace App\Http\Requests\Admin\PatternImage;

use Illuminate\Foundation\Http\FormRequest;

class UploadRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'images' => [
                'array',
                'required',
            ],
            'images.*' => 'image|mimes:jpeg,png,jpg|max:2048'
        ];
    }
}
