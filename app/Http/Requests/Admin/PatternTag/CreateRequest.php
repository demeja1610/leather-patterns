<?php

declare(strict_types=1);

namespace App\Http\Requests\Admin\PatternTag;

use App\Models\PatternTag;
use App\Models\PatternAuthor;
use App\Models\PatternCategory;
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
                'min:1',
                'unique:pattern_tags,name',
            ],
            'replace_id' => [
                'nullable',
                'numeric',
                'exists:pattern_tags,id',
            ],
            'replace_author_id' => [
                'nullable',
                'numeric',
                'exists:pattern_authors,id',
            ],
            'replace_category_id' => [
                'nullable',
                'numeric',
                'exists:pattern_categories,id',
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

    public function flashSelectedTag(): void
    {
        $tagId = $this->request->get('replace_id');

        if ($tagId !== null) {
            $tag = $this->getSelectedTag($tagId);

            if ($tag instanceof PatternTag) {
                $this->session()->flash('selectedReplace', $tag);
            }
        }
    }

    public function flashSelectedCategory(): void
    {
        $categoryId = $this->request->get('replace_category_id');

        if ($categoryId !== null) {
            $category = $this->getSelectedCategory($categoryId);

            if ($category instanceof PatternCategory) {
                $this->session()->flash('selectedReplaceCategory', $category);
            }
        }
    }

    public function flashSelectedAuthor(): void
    {
        $authorId = $this->request->get('replace_author_id');

        if ($authorId !== null) {
            $author = $this->getSelectedAuthor($authorId);

            if ($author instanceof PatternAuthor) {
                $this->session()->flash('selectedReplaceAuthor', $author);
            }
        }
    }

    protected function failedValidation(Validator $validator)
    {
        $this->flashSelectedTag();
        $this->flashSelectedCategory();
        $this->flashSelectedAuthor();

        return parent::failedValidation($validator);
    }

    protected function getSelectedTag($id): ?PatternTag
    {
        return PatternTag::query()
            ->where('id', $id)
            ->select([
                'id',
                'name'
            ])
            ->first();
    }

    protected function getSelectedCategory($id): ?PatternCategory
    {
        return PatternCategory::query()
            ->where('id', $id)
            ->select([
                'id',
                'name'
            ])
            ->first();
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
