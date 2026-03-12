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

        <div class="admin-page-single__grid admin-page-single__grid--3 ">
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
        </div>

        <x-input-image.preview
            id="images"
            name="images[]"
            :label="__('pattern.images')"
            :multiple="true"
            :url="route('api.admin.v1.pattern-image.upload')"
            :images="json_encode(array_map(array: old('images', []), callback: fn($url) => ['url' => $url, 'isNew' => true]), JSON_UNESCAPED_SLASHES)"
        />

        <x-input-file.preview
            id="files"
            name="files[]"
            :label="__('pattern.files')"
            :multiple="true"
            :url="route('api.admin.v1.pattern-file.upload')"
            :files="json_encode(old('files', []), JSON_UNESCAPED_SLASHES)"
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
