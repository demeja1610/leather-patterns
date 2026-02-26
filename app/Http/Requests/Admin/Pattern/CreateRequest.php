<?php

declare(strict_types=1);

namespace App\Http\Requests\Admin\Pattern;

use App\Models\PatternTag;
use App\Models\PatternAuthor;
use App\Enum\PatternSourceEnum;
use App\Models\PatternCategory;
use Illuminate\Validation\Rule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Database\Eloquent\Collection;
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

            'category_id' => [
                'nullable',
                'array',
            ],

            'category_id.*' => [
                'exists:pattern_categories,id',
            ],

            'tag_id' => [
                'nullable',
                'array',
            ],

            'tag_id.*' => [
                'exists:pattern_tags,id',
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

    public function flashSelectedCategories(): void
    {
        $categoriesIds = $this->request->all('category_id');

        if ($categoriesIds !== []) {
            $categories = $this->getSelectedCategories(...$categoriesIds);

            if ($categories->isEmpty() === false) {
                $this->session()->flash('selected_categories', $categories);
            }
        }
    }

    public function flashSelectedTags(): void
    {
        $tagIds = $this->request->all('tag_id');

        if ($tagIds !== []) {
            $tags = $this->getSelectedTags(...$tagIds);

            if ($tags->isEmpty() === false) {
                $this->session()->flash('selected_tags', $tags);
            }
        }
    }

    protected function failedValidation(Validator $validator)
    {
        $this->flashSelectedAuthor();

        $this->flashSelectedCategories();

        $this->flashSelectedTags();

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

    /**
     * @return \Illuminate\Database\Eloquent\Collection<\App\Models\PatternCategory>
     */
    protected function getSelectedCategories(...$ids): Collection
    {
        return PatternCategory::query()
            ->whereIn('id', $ids)
            ->select([
                'id',
                'name'
            ])
            ->get();
    }

    /**
     * @return \Illuminate\Database\Eloquent\Collection<\App\Models\PatternTag>
     */
    protected function getSelectedTags(...$ids): Collection
    {
        return PatternTag::query()
            ->whereIn('id', $ids)
            ->select([
                'id',
                'name'
            ])
            ->get();
    }
}
