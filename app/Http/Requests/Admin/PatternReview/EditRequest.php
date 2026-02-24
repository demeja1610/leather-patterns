<?php

declare(strict_types=1);

namespace App\Http\Requests\Admin\PatternReview;

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
            'reviewer_name' => [
                'required',
                'string',
                'max:255',
                'min:2',
            ],
            'comment' => [
                'nullable',
                'string',
                'min:2',
            ],
            'is_approved' => [
                'nullable',
                'in:on',
            ],
        ];
    }
}
