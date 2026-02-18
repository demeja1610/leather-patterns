@extends('layouts.admin.single', [
    'title' => __('pattern_category.edition'),
])

@section('page')
    <x-admin.form.edit
        :action="route('admin.pattern-category.update', ['id' => $category->id])"
        x-data="{
            categoryReplacements: {{ json_encode($categoryReplacements, JSON_UNESCAPED_UNICODE) }},
            selectedReplacementId: {{ $category->replacement?->id ?? 'null' }}
        }"
    >
        @method('PATCH')

        <x-input-text.input-text>
            <x-input-text.label for="name">
                {{ __('pattern_category.id') }}
            </x-input-text.label>

            <x-input-text.input
                id="id"
                name="nide"
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
                required
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
                @checked(old('remove_on_appear', $category->remove_on_appear))
            />
        </x-checkbox.custom>

        <x-button.default type="submit">
            {{ __('actions.save') }}
        </x-button.default>
    </x-admin.form.edit>
@endsection
