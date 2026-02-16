@props(['title' => null])

<div
    {{ $attributes->merge(['class' => 'modal']) }}
    x-cloak
    x-transition.duration.300ms
    x-on:keyup.escape.window="$dispatch('close-modal')"
>
    <div
        class="modal__overlay"
        x-on:click.self="$dispatch('close-modal')"
        title="{{ __('phrases.close_modal') }}"
    ></div>

    <div
        class="modal__content"
        @if ($title) title="{{ $title }}" @endif
    >
        <header class="modal__header">
            @if ($title)
                <h3 class="modal__title">
                    {{ $title }}
                </h3>
            @endif

            <button
                class="modal__close"
                x-on:click.prevent="$dispatch('close-modal')"
                title="{{ __('phrases.close_modal') }}"
            >
                <x-icon.svg
                    name="close"
                    class="modal__close-icon"
                />
            </button>
        </header>

        <div class="modal__body">
            {{ $slot }}
        </div>
    </div>
</div>
