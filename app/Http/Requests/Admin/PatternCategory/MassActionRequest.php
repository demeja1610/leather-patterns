<?php

declare(strict_types=1);

namespace App\Http\Requests\Admin\PatternCategory;

use App\Enum\ActionEnum;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class MassActionRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'ids' => 'required|array',
            'action' => ['required', 'string', Rule::enum(ActionEnum::class)],
        ];
    }
}
