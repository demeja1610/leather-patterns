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
                'mimetypes:application/pdf,image/vnd.dwg,application/zip,application/x-rar,application/x-7z-compressed,image/svg+xml,image/jpeg,image/png',
                'max:4098'
            ]
        ];
    }
}
