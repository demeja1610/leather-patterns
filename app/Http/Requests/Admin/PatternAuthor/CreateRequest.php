<?php

declare(strict_types=1);

namespace App\Http\Requests\Admin\PatternAuthor;

use App\Models\PatternAuthor;
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
            'name' => [
                'required',
                'string',
                'max:255',
                'min:2',
                'unique:pattern_authors,name',
            ],
            'replace_id' => [
                'nullable',
                'numeric',
                'exists:pattern_authors,id',
            ],
            'remove_on_appear' => [
                'nullable',
                'in:on',
            ],
            'is_published' => [
                'nullable',
                'in:on',
            ],
        ];
    }

    public function flashSelectedAuthor(): void
    {
        $authorId = $this->request->get('replace_id');

        if ($authorId !== null) {
            $author = $this->getSelectedAuthor($authorId);

            if ($author instanceof PatternAuthor) {
                $this->session()->flash('selectedReplace', $author);
            }
        }
    }

    protected function failedValidation(Validator $validator)
    {
        $this->flashSelectedAuthor();

        return parent::failedValidation($validator);
    }

    protected function getSelectedAuthor($id): ?PatternAuthor
    {
        return PatternAuthor::query()
            ->where('id', $id)
            ->select([
                'id',
                'name'
            ])
            ->first();
    }
}
