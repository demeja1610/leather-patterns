@props([
    'confirmText' => 'Confirm',
    'cancelText' => 'Cancel',
    'text' => 'Confirm your action',
])

<form
    {{ $attributes->merge(['class' => 'confirm-form']) }}
    method="POST"
>
    @csrf

    {{ $slot }}

    <p class="confirm-form__text">
        {{ $text }}
    </p>

    <div class="confirm-form__buttons">
        <button
            type="submit"
            class="button confirm-form__button confirm-form__button--delete"
        >
            {{ $confirmText }}
        </button>

        <button
            class="button button--ghost confirm-form__button confirm-form__button--cancel"
            x-on:click.prevent="$dispatch('cancel')"
        >
            {{ $cancelText }}
        </button>
    </div>
</form>
