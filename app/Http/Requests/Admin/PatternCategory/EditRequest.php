<?php

declare(strict_types=1);

namespace App\Http\Requests\Admin\PatternCategory;

use App\Http\Requests\Admin\PatternCategory\CreateRequest;

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
                    'min:2',
                    "unique:pattern_categories,name,{$this->id}",
                ],
            ],
        );
    }
}
