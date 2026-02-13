@extends('layouts.admin')

@section('content')
    <div class="admin-page">
        <x-admin.page-header.header title="{{ __('pattern_category.creation') }}">

        </x-admin.page-header.header>

        <form
            action="{{ route('admin.page.pattern-category.create') }}"
            method="POST"
            class="form"
        >
            @csrf

            <x-input-text.input-text>
                <x-input-text.label for="name">
                    {{ __('pattern_category.name') }}
                </x-input-text.label>

                <x-input-text.input
                    id="name"
                    name="name"
                    type="text"
                    required
                    :value="old('name')"
                    title="{{ __('pattern_category.name') }}e"
                />

                <x-input-text.input-errors :messages="$errors->get('name')" />
            </x-input-text.input-text>

            <button
                class="button"
                type="submit"
            >
                {{ __('actions.create') }}
            </button>
        </form>
    </div>
@endsection
