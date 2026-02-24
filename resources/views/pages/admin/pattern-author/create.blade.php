@extends('layouts.admin.single', [
    'title' => __('pattern_author.creation'),
])

@section('page')
    <x-admin.form.create
        :action="route('admin.page.pattern-author.create')"
        x-data="{
            authorReplacements: {{ json_encode($authorReplacements, JSON_UNESCAPED_UNICODE) }},
            selectedReplacementId: {{ old('replace_id') ?? 'null' }}
        }"
    >
        <x-input-text.input-text>
            <x-input-text.label
                for="name"
                class="required"
            >
                {{ __('pattern_author.name') }}
            </x-input-text.label>

            <x-input-text.input
                id="name"
                name="name"
                type="text"
                :required="true"
                :value="old('name')"
                title="{{ __('pattern_author.name') }}e"
            />

            <x-input-text.input-errors :messages="$errors->get('name')" />
        </x-input-text.input-text>

        <x-select.wrapper>
            <x-select.label for="replace_id">
                {{ __('pattern_author.replacement') }}
            </x-select.label>

            <x-select.select
                name="replace_id"
                id="replace_id"
                :title="__('pattern_author.replacement')"
                x-model.number="selectedReplacementId"
            >
                <x-select.option value="">
                    {{ __('filter.not_selected') }}
                </x-select.option>

                <template
                    x-for="authorReplacement in authorReplacements"
                    :key="authorReplacement.id"
                >
                    <x-select.option
                        x-bind:value="authorReplacement.id"
                        x-text="authorReplacement.name"
                        x-bind:selected="authorReplacement.id === selectedReplacementId"
                    >
                    </x-select.option>
                </template>
            </x-select.select>
        </x-select.wrapper>

        <x-checkbox.custom :label="__('pattern_author.remove_on_appear')">
            <input
                type="checkbox"
                class="checkbox__input"
                name="remove_on_appear"
                @checked(old('remove_on_appear') !== null)
            />
        </x-checkbox.custom>

        <x-checkbox.custom :label="__('pattern_author.is_published')">
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
