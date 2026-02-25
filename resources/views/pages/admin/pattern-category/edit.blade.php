@extends('layouts.admin.single', [
    'title' => __('pattern_category.edition'),
])

@section('page')
    <x-admin.form.edit :action="route('admin.pattern-category.update', ['id' => $category->id])">
        @method('PATCH')

        <x-input-text.input-text>
            <x-input-text.label for="name">
                {{ __('pattern_category.id') }}
            </x-input-text.label>

            <x-input-text.input
                id="id"
                name="id"
                type="text"
                disabled
                :value="$category->id"
                title="{{ __('pattern_category.id') }}"
            />
        </x-input-text.input-text>

        <x-input-text.input-text>
            <x-input-text.label
                for="name"
                class="required"
            >
                {{ __('pattern_category.name') }}
            </x-input-text.label>

            <x-input-text.input
                id="name"
                name="name"
                :value="old('name') ?? $category->name"
                title="{{ __('pattern_category.name') }}"
                :required="true"
            />

            <x-input-text.input-errors :messages="$errors->get('name')" />
        </x-input-text.input-text>

        <x-fetch-select.single
            :url="route('api.admin.v1.pattern-category.search-replace')"
            id="replace_id"
            name="replace_id"
            :label="__('pattern_category.replacement')"
            :placeholder="__('phrases.search')"
            :selectedKey="old('replace_id') ?? $category->replacement?->id"
            :selectedValue="session()->get('replace_name') ?? $category->replacement?->name"
        />

        <x-checkbox.custom :label="__('pattern_category.remove_on_appear')">
            <input
                type="checkbox"
                class="checkbox__input"
                name="remove_on_appear"
                @checked(old('remove_on_appear', $category->remove_on_appear))
            />
        </x-checkbox.custom>

        <x-checkbox.custom :label="__('pattern_category.is_published')">
            <input
                type="checkbox"
                class="checkbox__input"
                name="is_published"
                @checked(old('is_published', $category->is_published))
            />
        </x-checkbox.custom>

        <x-button.default type="submit">
            {{ __('actions.save') }}
        </x-button.default>
    </x-admin.form.edit>
@endsection
