<?php

declare(strict_types=1);

namespace App\Http\Requests\Admin\PatternAuthorSocial;

use App\Enum\SocialTypeEnum;
use Illuminate\Validation\Rule;
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
            'id' => [
                'nullable',
                'numeric',
            ],
            'type' => [
                'nullable',
                Rule::enum(SocialTypeEnum::class),
            ],
            'url' => [
                'nullable',
                'string',
            ],
            'author_id' => [
                'nullable',
                'numeric',
            ],
            'created_at' => [
                'nullable',
                'date',
            ],
        ];
    }
}
