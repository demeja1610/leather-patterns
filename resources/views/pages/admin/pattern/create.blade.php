@extends('layouts.admin.single', [
    'title' => __('pattern.creation'),
    'classes' => 'admin-page-patterns-create',
])

@section('page')
    <x-admin.form.create :action="route('admin.patterns.create')">
        <x-input-text.input-text>
            <x-input-text.label
                for="title"
                class="required"
            >
                {{ __('pattern.title') }}
            </x-input-text.label>

            <x-input-text.input
                id="title"
                name="title"
                type="text"
                :required="true"
                :value="old('title')"
                title="{{ __('pattern.title') }}"
            />

            <x-input-text.input-errors :messages="$errors->get('title')" />
        </x-input-text.input-text>

        <x-select.wrapper>
            <x-select.label
                for="source"
                class="required"
            >
                {{ __('pattern.source') }}
            </x-select.label>

            <x-select.select
                name="source"
                id="source"
                :title="__('pattern.source')"
                required
            >
                <x-select.option
                    value=""
                    :selected="old('source') === null"
                >
                    {{ __('filter.not_selected') }}
                </x-select.option>

                @foreach ($sources as $source)
                    <x-select.option
                        :value="$source->value"
                        :selected="old('source') === $source->value"
                    >
                        {{ __("pattern_source.{$source->value}") }}
                    </x-select.option>
                @endforeach

            </x-select.select>
        </x-select.wrapper>

        <x-input-text.input-text>
            <x-input-text.label for="source_url">
                {{ __('pattern.source_url') }}
            </x-input-text.label>

            <x-input-text.input
                id="source_url"
                name="source_url"
                type="url"
                :value="old('source_url')"
                title="{{ __('pattern.source_url') }}"
            />

            <x-input-text.input-errors :messages="$errors->get('source_url')" />
        </x-input-text.input-text>

        <x-fetch-select.single
            :url="route('api.admin.v1.pattern-author.search', ['pattern_replaceable' => 0, 'pattern_removable' => 0])"
            id="author_id"
            name="author_id"
            :label="__('pattern.author')"
            :placeholder="__('phrases.search')"
            selectedItemOptionValueName="id"
            selectedItemOptionLabelName="name"
            :selectedItem="session()
                ->get('selected_author')
                ?->toJson(JSON_UNESCAPED_UNICODE)"
        />

        <x-fetch-select.multiple
            :url="route('api.admin.v1.pattern-category.search', ['pattern_replaceable' => 0, 'pattern_removable' => 0])"
            id="category_id"
            name="category_id[]"
            :label="__('pattern.categories')"
            :placeholder="__('phrases.search')"
            selectedItemOptionValueName="id"
            selectedItemOptionLabelName="name"
            :selectedItems="session()
                ->get('selected_categories')
                ?->toJson(JSON_UNESCAPED_UNICODE)"
        />

        <x-fetch-select.multiple
            :url="route('api.admin.v1.pattern-tag.search', ['pattern_replaceable' => 0, 'pattern_removable' => 0])"
            id="tag_id"
            name="tag_id[]"
            :label="__('pattern.tags')"
            :placeholder="__('phrases.search')"
            selectedItemOptionValueName="id"
            selectedItemOptionLabelName="name"
            :selectedItems="session()
                ->get('selected_tags')
                ?->toJson(JSON_UNESCAPED_UNICODE)"
        />

        <x-checkbox.custom :label="__('pattern.is_published')">
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
