@extends('layouts.admin.single', [
    'title' => __('pattern_tag.creation'),
])

@section('page')
    <x-admin.form.create :action="route('admin.page.pattern-tag.create')">
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
                type="text"
                :required="true"
                :value="old('name')"
                title="{{ __('pattern_tag.name') }}e"
            />

            <x-input-text.input-errors :messages="$errors->get('name')" />
        </x-input-text.input-text>

        <x-fetch-select.single
            :url="route('api.admin.v1.pattern-tag.search-replace')"
            id="replace_id"
            name="replace_id"
            :label="__('pattern_tag.replacement')"
            :placeholder="__('phrases.search')"
            keyName="id"
            valueName="name"
            :selectedKey="old('replace_id')"
            :selectedValue="session()->get('replace_name')"
        />

        <x-fetch-select.single
            :url="route('api.admin.v1.pattern-author.search-replace')"
            id="replace_author_id"
            name="replace_author_id"
            :label="__('pattern_tag.author_replacement')"
            :placeholder="__('phrases.search')"
            keyName="id"
            valueName="name"
            :selectedKey="old('replace_author_id')"
            :selectedValue="session()->get('replace_author_name')"
        />

        <x-fetch-select.single
            :url="route('api.admin.v1.pattern-category.search-replace')"
            id="replace_category_id"
            name="replace_category_id"
            :label="__('pattern_tag.category_replacement')"
            :placeholder="__('phrases.search')"
            keyName="id"
            valueName="name"
            :selectedKey="old('replace_category_id')"
            :selectedValue="session()->get('replace_category_name')"
        />

        <x-checkbox.custom :label="__('pattern_tag.remove_on_appear')">
            <input
                type="checkbox"
                class="checkbox__input"
                name="remove_on_appear"
                @checked(old('remove_on_appear') !== null)
            />
        </x-checkbox.custom>

        <x-checkbox.custom :label="__('pattern_tag.is_published')">
            <input
                type="checkbox"
                class="checkbox__input"
                name="is_published"
                @checked(old('is_published') !== null)
            />
        </x-checkbox.custom>

        <x-button.default type="submit">
            {{ __('actions.create') }}
        </x-button.default>
    </x-admin.form.create>
@endsection
