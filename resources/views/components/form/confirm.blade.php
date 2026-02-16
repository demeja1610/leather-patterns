@props([
    'confirmText' => __('actions.confirm'),
    'cancelText' => __('actions.cancel'),
    'text' => __('phrases.confirm_text'),
])

<form
    {{ $attributes->merge(['class' => 'confirm-form']) }}
    method="GET"
>
    @csrf

    {{ $slot }}

    <p class="confirm-form__text">
        {{ $text }}
    </p>

    <div class="confirm-form__buttons">
        <x-button.default
            class="confirm-form__button"
            :title="$confirmText"
        >
            {{ $confirmText }}
        </x-button.default>

        <x-button.ghost
            x-on:click.prevent="$dispatch('cancel')"
            class="confirm-form__button"
            :title="$cancelText"
        >
            {{ $cancelText }}
        </x-button.ghost>
    </div>
</form>
