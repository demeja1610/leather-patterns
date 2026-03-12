<?php

declare(strict_types=1);

namespace App\Http\Requests\Admin\Pattern;

use App\Http\Requests\Admin\Pattern\CreateRequest;

class EditRequest extends CreateRequest
{
    public function rules(): array
    {
        return array_merge(
            parent::rules(),
            [
                'remove_images' => [
                    'nullable',
                    'array',
                ],

                'remove_images*' => [
                    'url',
                ],

                'remove_files' => [
                    'nullable',
                    'array',
                ],

                'remove_files*' => [
                    'url',
                ],
            ]
        );
    }
}
