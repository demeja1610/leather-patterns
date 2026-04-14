<?php

declare(strict_types=1);

namespace App\Http\Requests\Admin\PatternVideo;

use App\Models\Pattern;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Contracts\Validation\Validator;

class CreateRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'url' => [
                'required',
                'url',
                'max:255',
            ],
            'pattern_id' => [
                'nullable',
                'numeric',
                'exists:patterns,id',
            ],

        ];
    }

    public function flashSelectePattern(): void
    {
        $patternId = $this->request->get('pattern_id');

        if ($patternId !== null) {
            $pattern = $this->getSelectedPattern($patternId);

            if ($pattern instanceof Pattern) {
                $this->session()->flash('selectedPattern', $pattern);
            }
        }
    }

    protected function failedValidation(Validator $validator)
    {
        $this->flashSelectePattern();

        return parent::failedValidation($validator);
    }

    protected function getSelectedPattern($id): ?Pattern
    {
        return Pattern::query()
            ->where('id', $id)
            ->select([
                'id',
                'title'
            ])
            ->first();
    }
}
