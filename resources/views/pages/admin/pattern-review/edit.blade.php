@extends('layouts.admin.single', [
    'title' => __('pattern_review.edition'),
])

@section('page')
    <x-admin.form.edit :action="route('admin.pattern-review.update', ['id' => $review->id])">
        @method('PATCH')

        <x-input-text.input-text>
            <x-input-text.label for="id">
                {{ __('pattern_review.id') }}
            </x-input-text.label>

            <x-input-text.input
                id="id"
                name="id"
                type="text"
                disabled
                :value="$review->id"
                title="{{ __('pattern_review.id') }}"
            />
        </x-input-text.input-text>

        @if ($review->user !== null)
            <x-input-text.input-text>
                <x-input-text.label for="user">
                    {{ __('pattern_review.user') }}
                </x-input-text.label>

                <x-input-text.input
                    id="user"
                    name="user"
                    type="text"
                    disabled
                    :value="$review->user?->name"
                    title="{{ __('pattern_review.user') }}"
                />
            </x-input-text.input-text>
        @endif

        <x-input-text.input-text>
            <x-input-text.label for="pattern">
                {{ __('pattern_review.pattern') }}

                <x-link.default
                    :href="route('page.pattern.single', ['id' => $review->pattern->id])"
                    target="_blank"
                >
                    <x-icon.svg name="external-link" />
                </x-link.default>
            </x-input-text.label>

            <x-input-text.input
                id="pattern"
                name="pattern"
                type="pattern"
                disabled
                :value="$review->pattern?->title"
                title="{{ __('pattern_review.pattern') }}"
            />
        </x-input-text.input-text>

        <x-input-text.input-text>
            <x-input-text.label
                for="reviewer_name"
                class="required"
            >
                {{ __('pattern_review.reviewer_name') }}
            </x-input-text.label>

            <x-input-text.input
                id="reviewer_name"
                name="reviewer_name"
                :value="old('reviewer_name') ?? $review->reviewer_name"
                title="{{ __('pattern_review.reviewer_name') }}"
                :required="true"
            />

            <x-input-text.input-errors :messages="$errors->get('reviewer_name')" />
        </x-input-text.input-text>

        <x-textarea.textarea>
            <x-textarea.label for="comment">
                {{ __('pattern_review.comment') }}
            </x-textarea.label>

            <x-textarea.input
                id="comment"
                name="comment"
                title="{{ __('pattern_review.comment') }}"
            >
                {{ $review->comment }}
            </x-textarea.input>

            <x-textarea.textarea-errors :messages="$errors->get('comment')" />
        </x-textarea.textarea>

        <x-checkbox.custom :label="__('pattern_review.is_approved')">
            <input
                type="checkbox"
                class="checkbox__input"
                name="is_approved"
                @checked(old('is_approved', $review->is_approved))
            />
        </x-checkbox.custom>

        <x-button.default type="submit">
            {{ __('actions.save') }}
        </x-button.default>
    </x-admin.form.edit>
@endsection
