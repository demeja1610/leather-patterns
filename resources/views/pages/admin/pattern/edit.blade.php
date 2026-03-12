@extends('layouts.admin.single', [
    'title' => __('pattern.edition'),
])

@section('page')
    <x-admin.form.edit :action="route('admin.patterns.update', ['id' => $pattern->id])">
        @method('PATCH')

        <x-input-text.input-text>
            <x-input-text.label for="id">
                {{ __('pattern.id') }}
            </x-input-text.label>

            <x-input-text.input
                id="id"
                name="id"
                type="text"
                disabled
                :value="$pattern->id"
                title="{{ __('pattern.id') }}"
            />
        </x-input-text.input-text>

        @if ($pattern->isParsed())
            <x-input-text.input-text>
                <x-input-text.label for="id">
                    {{ __('pattern.source') }}
                </x-input-text.label>

                <x-input-text.input
                    id="source"
                    name="source"
                    type="text"
                    disabled
                    :value="__('pattern_source.' . $pattern->source->value)"
                    :title="__('pattern.source')"
                />
            </x-input-text.input-text>
        @endif

        @if ($pattern->source_url !== null)
            <x-input-text.input-text>
                <x-input-text.label for="id">
                    {{ __('pattern.source_url') }}
                </x-input-text.label>

                <x-input-text.input
                    id="source"
                    name="source"
                    type="text"
                    disabled
                    :value="$pattern->source_url"
                    title="{{ __('pattern.source_url') }}"
                />
            </x-input-text.input-text>
        @endif

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
                :value="old('title', $pattern->title)"
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
                    ->get('selected_author', $pattern->author)
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
                    ->get('selected_categories', $pattern->categories)
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
                    ->get('selected_tags', $pattern->tags)
                    ?->toJson(JSON_UNESCAPED_UNICODE)"
            />
        </div>

        <x-input-image.preview
            id="images"
            name="images[]"
            :label="__('pattern.images')"
            :multiple="true"
            :url="route('api.admin.v1.pattern-image.upload')"
            :images="json_encode(
                array_merge(
                    array_map(array: old('images', []), callback: fn($url) => ['url' => $url, 'isNew' => true]),
                    array_map(array: $pattern->images->pluck('path')->toArray(), callback: fn($path) => ['url' => asset('/storage/' . $path), 'isNew' => false]),
                ),
                JSON_UNESCAPED_SLASHES,
            )"
        />

        <x-input-file.preview
            id="files"
            name="files[]"
            :label="__('pattern.files')"
            :multiple="true"
            :url="route('api.admin.v1.pattern-file.upload')"
            :files="json_encode(
                array_merge(
                    old('files', []),
                    array_map(
                        array: $pattern->files->select('id', 'path')->toArray(),
                        callback: function ($file) {
                            $file['url'] = asset('/storage/' . $file['path']);
                            return $file;
                        },
                    ),
                ),
                JSON_UNESCAPED_SLASHES,
            )"
        />

        <x-checkbox.custom :label="__('pattern.is_published')">
            <input
                type="checkbox"
                class="checkbox__input"
                name="is_published"
                @checked(old('is_published', $pattern->is_published))
            />
        </x-checkbox.custom>

        <x-button.default type="submit">
            {{ __('actions.save') }}
        </x-button.default>
    </x-admin.form.edit>
@endsection
