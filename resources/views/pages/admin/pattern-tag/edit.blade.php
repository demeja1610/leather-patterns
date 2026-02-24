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
            :url="route('api.admin.v1.pattern-tag.search-replace')"
            id="replace_id"
            name="replace_id"
            :label="__('pattern_tag.replacement')"
            :placeholder="__('phrases.search')"
            :selectedKey="$tag->replacement?->id"
            :selectedValue="$tag->replacement?->name"
        />

        <x-fetch-select.single
            :url="route('api.admin.v1.pattern-author.search-replace')"
            id="replace_author_id"
            name="replace_author_id"
            :label="__('pattern_tag.author_replacement')"
            :placeholder="__('phrases.search')"
            :selectedKey="$tag->authorReplacement?->id"
            :selectedValue="$tag->authorReplacement?->name"
        />

        <x-fetch-select.single
            :url="route('api.admin.v1.pattern-category.search-replace')"
            id="replace_category_id"
            name="replace_category_id"
            :label="__('pattern_tag.category_replacement')"
            :placeholder="__('phrases.search')"
            :selectedKey="$tag->categoryReplacement?->id"
            :selectedValue="$tag->categoryReplacement?->name"
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
