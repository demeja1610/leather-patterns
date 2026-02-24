@extends('layouts.admin.single', [
    'title' => __('pattern_tag.creation'),
])

@section('page')
    <x-admin.form.create
        :action="route('admin.page.pattern-tag.create')"
        x-data="{
            tagReplacements: {{ json_encode($tagReplacements, JSON_UNESCAPED_UNICODE) }},
            authorReplacements: {{ json_encode($authorReplacements, JSON_UNESCAPED_UNICODE) }},
            categoryReplacements: {{ json_encode($categoryReplacements, JSON_UNESCAPED_UNICODE) }},
            selectedReplacementId: {{ old('replace_id') ?? 'null' }},
            selectedAuthorReplacementId: {{ old('replace_author_id') ?? 'null' }},
            selectedCategoryReplacementId: {{ old('replace_category_id') ?? 'null' }},
        }"
    >
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

        <x-select.wrapper>
            <x-select.label for="replace_id">
                {{ __('pattern_tag.replacement') }}
            </x-select.label>

            <x-select.select
                name="replace_id"
                id="replace_id"
                :title="__('pattern_tag.replacement')"
                x-model.number="selectedReplacementId"
            >
                <x-select.option value="">
                    {{ __('filter.not_selected') }}
                </x-select.option>

                <template
                    x-for="tagReplacement in tagReplacements"
                    :key="tagReplacement.id"
                >
                    <x-select.option
                        x-bind:value="tagReplacement.id"
                        x-text="tagReplacement.name"
                        x-bind:selected="tagReplacement.id === selectedReplacementId"
                    >
                    </x-select.option>
                </template>
            </x-select.select>
        </x-select.wrapper>

        <x-select.wrapper>
            <x-select.label for="replace_author_id">
                {{ __('pattern_tag.author_replacement') }}
            </x-select.label>

            <x-select.select
                name="replace_author_id"
                id="replace_author_id"
                :title="__('pattern_tag.author_replacement')"
                x-model.number="selectedAuthorReplacementId"
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
                        x-bind:selected="authorReplacement.id === selectedAuthorReplacementId"
                    >
                    </x-select.option>
                </template>
            </x-select.select>
        </x-select.wrapper>

        <x-select.wrapper>
            <x-select.label for="replace_category_id">
                {{ __('pattern_tag.category_replacement') }}
            </x-select.label>

            <x-select.select
                name="replace_category_id"
                id="replace_category_id"
                :title="__('pattern_tag.category_replacement')"
                x-model.number="selectedCategoryReplacementId"
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
                        x-bind:selected="categoryReplacement.id === selectedCategoryReplacementId"
                    >
                    </x-select.option>
                </template>
            </x-select.select>
        </x-select.wrapper>

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
