@extends('layouts.admin.single', [
    'title' => __('pattern_category.creation'),
])

@section('page')
    <x-admin.form.create :action="route('admin.page.pattern-category.create')">
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
                type="text"
                :required="true"
                :value="old('name')"
                title="{{ __('pattern_category.name') }}e"
            />

            <x-input-text.input-errors :messages="$errors->get('name')" />
        </x-input-text.input-text>

        <x-fetch-select.single
            :url="route('api.admin.v1.pattern-category.search-replace')"
            id="replace_id"
            name="replace_id"
            :label="__('pattern_category.replacement')"
            :placeholder="__('phrases.search')"
            selectedItemOptionValueName="id"
            selectedItemOptionLabelName="name"
            :selectedItem="session()
                ->get('selectedReplace')
                ?->toJson(JSON_UNESCAPED_UNICODE)"
            :required="true"
        />

        <x-checkbox.custom :label="__('pattern_category.remove_on_appear')">
            <input
                type="checkbox"
                class="checkbox__input"
                name="remove_on_appear"
                @checked(old('remove_on_appear') !== null)
            />
        </x-checkbox.custom>

        <x-checkbox.custom :label="__('pattern_category.is_published')">
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
