@extends('layouts.admin.single', [
    'title' => __('pattern_video.creation'),
])

@section('page')
    <x-admin.form.create :action="route('admin.page.pattern-videos.create')">
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
                :required="true"
                :value="old('url')"
                title="{{ __('pattern_video.url') }}"
            />

            <x-input-text.input-errors :messages="$errors->get('url')" />
        </x-input-text.input-text>

        <x-fetch-select.single
            :url="route('api.admin.v1.pattern.search',)"
            id="pattern_id"
            name="pattern_id"
            :label="__('pattern_video.pattern')"
            :placeholder="__('phrases.search')"
            selectedItemOptionValueName="id"
            selectedItemOptionLabelName="title"
           :selectedItem="session()
                ->get('selectedPattern')
                ?->toJson(JSON_UNESCAPED_UNICODE)"
        />

        <x-button.default type="submit">
            {{ __('actions.create') }}
        </x-button.default>
    </x-admin.form.create>
@endsection
