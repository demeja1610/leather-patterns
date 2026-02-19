@extends('layouts.admin.single', [
    'title' => __('pattern_category.creation'),
])

@section('page')
    <x-admin.form.create
        :action="route('admin.page.pattern-category.create')"
        x-data="{
            categoryReplacements: {{ json_encode($categoryReplacements, JSON_UNESCAPED_UNICODE) }},
            selectedReplacementId: {{ old('replace_id') ?? 'null' }}
        }"
    >
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
                required
                :value="old('name')"
                title="{{ __('pattern_category.name') }}e"
            />

            <x-input-text.input-errors :messages="$errors->get('name')" />
        </x-input-text.input-text>

        <x-select.wrapper>
            <x-select.label for="replace_id">
                {{ __('pattern_category.replacement') }}
            </x-select.label>

            <x-select.select
                name="replace_id"
                id="replace_id"
                :title="__('pattern_category.replacement')"
                x-model.number="selectedReplacementId"
            >
                <x-select.option value="">
                    {{ __('filter.not_selected') }}
                </x-select.option>

                <template
                    x-for="categoryReplacement in categoryReplacements"
                    :key="categoryReplacement.id"
                >
                    <x-select.option
                        x-bind:value="categoryReplacement.id"
                        x-text="categoryReplacement.name"
                        x-bind:selected="categoryReplacement.id === selectedReplacementId"
                    >
                    </x-select.option>
                </template>
            </x-select.select>
        </x-select.wrapper>

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
