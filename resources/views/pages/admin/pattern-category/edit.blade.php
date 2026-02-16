@extends('layouts.admin')

@section('content')
    <div class="admin-page">
        <x-admin.page-header.header title="{{ __('pattern_category.edition') }}">

        </x-admin.page-header.header>

        <form
            action="{{ route('admin.pattern-category.update', ['id' => $category->id]) }}"
            method="POST"
            class="form"
        >
            @csrf
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
                <x-input-text.label for="name">
                    {{ __('pattern_category.name') }}
                </x-input-text.label>

                <x-input-text.input
                    id="name"
                    name="name"
                    type="text"
                    :value="old('name') ?? $category->name"
                    title="{{ __('pattern_category.name') }}"
                />

                <x-input-text.input-errors :messages="$errors->get('name')" />
            </x-input-text.input-text>

            <x-button.default type="submit">
                {{ __('actions.save') }}
            </x-button.default>
        </form>
    </div>
@endsection
