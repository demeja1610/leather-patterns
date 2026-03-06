<?php

declare(strict_types=1);

namespace App\Http\Requests\Admin\PatternCategory;

use App\Models\PatternTag;
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
                'min:2',
                'unique:pattern_categories,name',
            ],
            'replace_id' => [
                'nullable',
                'numeric',
                'exists:pattern_categories,id',
            ],
            'replace_tag_id' => [
                'nullable',
                'numeric',
                'exists:pattern_tags,id',
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
        $tagId = $this->request->get('replace_tag_id');

        if ($tagId !== null) {
            $tag = $this->getSelectedTag($tagId);

            if ($tag instanceof PatternTag) {
                $this->session()->flash('selectedTagReplace', $tag);
            }
        }
    }

    public function flashSelectedCategory(): void
    {
        $categoryId = $this->request->get('replace_id');

        if ($categoryId !== null) {
            $category = $this->getSelectedCategory($categoryId);

            if ($category instanceof PatternCategory) {

                $this->session()->flash('selectedReplace', $category);
            }
        }
    }

    protected function failedValidation(Validator $validator)
    {
        $this->flashSelectedTag();
        $this->flashSelectedCategory();

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
}
