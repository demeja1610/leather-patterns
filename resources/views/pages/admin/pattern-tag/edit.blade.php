@extends('layouts.admin.single', [
    'title' => __('pattern_tag.edition'),
])

@section('page')
    <x-admin.form.edit :action="route('admin.pattern-tag.update', ['id' => $tag->id])">
        @method('PATCH')

        <x-input-text.input-text>
            <x-input-text.label for="name">
                {{ __('pattern_tag.id') }}
            </x-input-text.label>

            <x-input-text.input
                id="id"
                name="id"
                type="text"
                disabled
                :value="$tag->id"
                title="{{ __('pattern_tag.id') }}"
            />
        </x-input-text.input-text>

        <x-input-text.input-text>
            <x-input-text.label
                for="name"
                class="required"
            >
                {{ __('pattern_tag.name') }}
            </x-input-text.label>

            <x-input-text.input
                id="name"
                name="name"
                :value="old('name') ?? $tag->name"
                title="{{ __('pattern_tag.name') }}"
                :required="true"
            />

            <x-input-text.input-errors :messages="$errors->get('name')" />
        </x-input-text.input-text>

        <x-fetch-select.single
            :url="route('api.admin.v1.pattern-tag.search', [
                'except_id' => $tag->id,
                'pattern_replaceable' => 0,
                'pattern_removable' => 0,
            ])"
            id="replace_id"
            name="replace_id"
            :label="__('pattern_tag.replacement')"
            :placeholder="__('phrases.search')"
            selectedItemOptionValueName="id"
            selectedItemOptionLabelName="name"
            :selectedItem="session()
                ->get('selectedReplace', $tag->replacement)
                ?->toJson(JSON_UNESCAPED_UNICODE)"
        />

        <x-fetch-select.single
            :url="route('api.admin.v1.pattern-author.search', ['pattern_replaceable' => 0, 'pattern_removable' => 0])"
            id="replace_author_id"
            name="replace_author_id"
            :label="__('pattern_tag.author_replacement')"
            :placeholder="__('phrases.search')"
            selectedItemOptionValueName="id"
            selectedItemOptionLabelName="name"
            :selectedItem="session()
                ->get('selectedReplaceAuthor', $tag->authorReplacement)
                ?->toJson(JSON_UNESCAPED_UNICODE)"
        />

        <x-fetch-select.single
            :url="route('api.admin.v1.pattern-category.search', ['pattern_replaceable' => 0, 'pattern_removable' => 0])"
            id="replace_category_id"
            name="replace_category_id"
            :label="__('pattern_tag.category_replacement')"
            :placeholder="__('phrases.search')"
            selectedItemOptionValueName="id"
            selectedItemOptionLabelName="name"
            :selectedItem="session()
                ->get('selectedReplaceCategory', $tag->categoryReplacement)
                ?->toJson(JSON_UNESCAPED_UNICODE)"
        />

        <x-checkbox.custom :label="__('pattern_tag.remove_on_appear')">
            <input
                type="checkbox"
                class="checkbox__input"
                name="remove_on_appear"
                @checked(old('remove_on_appear', $tag->remove_on_appear))
            />
        </x-checkbox.custom>

        <x-checkbox.custom :label="__('pattern_tag.is_published')">
            <input
                type="checkbox"
                class="checkbox__input"
                name="is_published"
                @checked(old('is_published', $tag->is_published))
            />
        </x-checkbox.custom>

        <x-button.default type="submit">
            {{ __('actions.save') }}
        </x-button.default>
    </x-admin.form.edit>
@endsection
