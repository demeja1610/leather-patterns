<?php

namespace App\Http\Requests\Admin\PatternFile;

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
            'files' => [
                'array',
                'required',
            ],
            'files.*' => [
                'file',
                'mimetypes:application/pdf,application/zip',
                'max:32768'
            ]
        ];
    }
}
