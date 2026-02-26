<?php

declare(strict_types=1);

namespace App\Http\Requests\Admin\Pattern;

use App\Enum\PatternSourceEnum;
use App\Http\Requests\Admin\Pattern\CreateRequest;

class EditRequest extends CreateRequest
{
    public function rules(): array
    {
        $localSource = PatternSourceEnum::LOCAL;

        return array_merge(
            parent::rules(),
            [
                'source_url' => [
                    'nullable',
                    "required_unless:source,{$localSource->value}",
                    'url',
                    "unique:patterns,source_url, $this->id",
                ],
            ]
        );
    }
}
