<?php

declare(strict_types=1);

namespace App\Http\Requests\Admin\Pattern;

use App\Enum\PatternSourceEnum;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class CreateRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $localSource = PatternSourceEnum::LOCAL;

        return [
            'title' => [
                'required',
                'string',
                'max:255',
                'min:2',
            ],

            'source' => [
                'required',
                Rule::enum(PatternSourceEnum::class),
            ],
            'source_url' => [
                'nullable',
                "required_unless:source,{$localSource->value}",
                'url',
                'unique:patterns,source_url',
            ],
            'is_published' => [
                'nullable',
                'in:on',
            ],
        ];
    }
}
