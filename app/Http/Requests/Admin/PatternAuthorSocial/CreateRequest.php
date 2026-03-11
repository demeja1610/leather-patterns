<?php

declare(strict_types=1);

namespace App\Http\Requests\Admin\PatternAuthorSocial;

use App\Enum\SocialTypeEnum;
use App\Models\PatternAuthor;
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
        return [
            'url' => [
                'url',
            ],
            'author_id' => [
                'numeric',
                'exists:pattern_authors,id',
            ],
            'is_published' => [
                'nullable',
                'in:on',
            ],
        ];
    }

    public function flashSelectedAuthor(): void
    {
        $authorId = $this->request->get('author_id');

        if ($authorId !== null) {
            $author = $this->getSelectedAuthor($authorId);

            if ($author instanceof PatternAuthor) {
                $this->session()->flash('selected_author', $author);
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
