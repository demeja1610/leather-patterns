@extends('layouts.admin.single', [
    'title' => __('pattern_video.edition'),
])

@section('page')
    <x-admin.form.edit :action="route('admin.pattern-videos.update', ['id' => $video->id])">
        @method('PATCH')

        <x-input-text.input-text>
            <x-input-text.label for="id">
                {{ __('pattern_video.id') }}
            </x-input-text.label>

            <x-input-text.input
                id="id"
                name="id"
                type="text"
                disabled
                :value="$video->id"
                title="{{ __('pattern_video.id') }}"
            />
        </x-input-text.input-text>

        <x-input-text.input-text>
            <x-input-text.label
                for="url"
                class="required"
            >
                {{ __('pattern_video.url') }}
            </x-input-text.label>

            <x-input-text.input
                id="url"
                name="url"
                type="url"
                :value="old('url') ?? $video->url"
                title="{{ __('pattern_video.url') }}"
                :required="true"
            />

            <x-input-text.input-errors :messages="$errors->get('url')" />
        </x-input-text.input-text>

        <x-fetch-select.single
            :url="route('api.admin.v1.pattern.search')"
            id="pattern_id"
            name="pattern_id"
            :label="__('pattern_video.pattern')"
            :placeholder="__('phrases.search')"
            selectedItemOptionValueName="id"
            selectedItemOptionLabelName="title"
            :selectedItem="session()
                ->get('selectedPattern', $video->pattern)
                ?->toJson(JSON_UNESCAPED_UNICODE)"
        />

        <x-button.default type="submit">
            {{ __('actions.save') }}
        </x-button.default>
    </x-admin.form.edit>
@endsection
