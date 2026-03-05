@extends('layouts.admin.single', [
    'title' => __('pattern_author_social.edition'),
])

@section('page')
    <x-admin.form.edit :action="route('admin.pattern-author-social.update', ['id' => $social->id])">
        @method('PATCH')

        <x-select.wrapper>
            <x-select.label
                for="type"
                class="required"
            >
                {{ __('pattern_author_social.type') }}
            </x-select.label>

            <x-select.select
                name="type"
                id="type"
                :title="__('pattern_author_social.type')"
                required
            >
                <x-select.option
                    value=""
                    :selected="old('type', $social->type) === null"
                >
                    {{ __('filter.not_selected') }}
                </x-select.option>

                @foreach ($types as $type)
                    <x-select.option
                        :value="$type->value"
                        :selected="old('type', $social->type->value) === $type->value"
                    >
                        {{ __("pattern_author_social.types.{$type->value}") }}
                    </x-select.option>
                @endforeach
            </x-select.select>

            <x-select.errors :messages="$errors->get('type')" />
        </x-select.wrapper>

        <x-input-text.input-text>
            <x-input-text.label
                for="url"
                class="required"
            >
                {{ __('pattern_author_social.url') }}
            </x-input-text.label>

            <x-input-text.input
                id="url"
                name="url"
                type="text"
                :required="true"
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
