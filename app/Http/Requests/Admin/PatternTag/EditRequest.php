<?php

declare(strict_types=1);

namespace App\Http\Requests\Admin\PatternTag;


class EditRequest extends CreateRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return array_merge(
            parent::rules(),
            [
                'name' => [
                    'required',
                    'string',
                    'max:255',
                    'min:1',
                    "unique:pattern_tags,name,{$this->id}",
                ],
            ],
        );
    }
}
