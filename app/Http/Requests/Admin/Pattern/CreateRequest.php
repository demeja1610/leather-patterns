<?php

declare(strict_types=1);

namespace App\Http\Requests\Admin\Pattern;

use App\Models\PatternAuthor;
use App\Enum\PatternSourceEnum;
use Illuminate\Validation\Rule;
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

            'author_id' => [
                'nullable',
                'numeric',
                'exists:pattern_authors,id',
            ],

            'is_published' => [
                'nullable',
                'in:on',
            ],
        ];
    }

    protected function failedValidation(Validator $validator)
    {
        $authorId = $this->request->get('author_id');

        if ($authorId !== null) {
            $author = PatternAuthor::query()->where('id', $authorId)->select(['id', 'name'])->first();

            if ($author instanceof PatternAuthor) {
                $this->session()->flash('selectedAuthor', $author);
            }
        }

        return parent::failedValidation($validator);
    }
}
