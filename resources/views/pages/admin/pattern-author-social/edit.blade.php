@extends('layouts.admin.single', [
    'title' => __('pattern_author_social.edition'),
])

@section('page')
    <x-admin.form.edit :action="route('admin.pattern-author-socials.update', ['id' => $social->id])">
        @method('PATCH')

        <x-input-text.input-text>
            <x-input-text.label for="url">
                {{ __('pattern_author_social.url') }}
            </x-input-text.label>

            <x-input-text.input
                id="url"
                name="url"
                type="text"
                :value="old('url', $social->url)"
                title="{{ __('pattern_author_social.url') }}"
            />

            <x-input-text.input-errors :messages="$errors->get('url')" />
        </x-input-text.input-text>

        <x-fetch-select.single
            :url="route('api.admin.v1.pattern-author.search', ['pattern_replaceable' => 0, 'pattern_removable' => 0])"
            id="author_id"
            name="author_id"
            :required="true"
            :label="__('pattern_author_social.author')"
            :placeholder="__('phrases.search')"
            selectedItemOptionValueName="id"
            selectedItemOptionLabelName="name"
            :selectedItem="session()
                ->get('selected_author', $social->author)
                ?->toJson(JSON_UNESCAPED_UNICODE)"
        />

        <x-checkbox.custom :label="__('pattern_author_social.is_published')">
            <input
                type="checkbox"
                class="checkbox__input"
                name="is_published"
                @checked(old('is_published', $social->is_published))
            />
        </x-checkbox.custom>

        <x-button.default type="submit">
            {{ __('actions.save') }}
        </x-button.default>
    </x-admin.form.edit>
@endsection
