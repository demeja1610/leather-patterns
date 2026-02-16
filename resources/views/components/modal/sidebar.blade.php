@props(['title' => null])

<div
    {{ $attributes->merge(['class' => 'modal-sidebar']) }}
    x-cloak
    x-transition.duration.300ms
    x-on:keyup.escape.window="$dispatch('close-modal')"
>
    <div
        class="modal-sidebar__overlay"
        x-on:click.self="$dispatch('close-modal')"
        title="{{ __('phrases.close_modal') }}"
    ></div>

    <div
        class="modal-sidebar__content"
        @if ($title) title="{{ $title }}" @endif
    >
        <header class="modal-sidebar__header">
            @if ($title)
                <h3 class="modal-sidebar__title">
                    {{ $title }}
                </h3>
            @endif

            <button
                class="modal-sidebar__close"
                x-on:click.prevent="$dispatch('close-modal')"
                title="{{ __('phrases.close_modal') }}"
            >
                <x-icon.svg
                    name="close"
                    class="modal-sidebar__close-icon"
                />
            </button>
        </header>

        <div class="modal-sidebar__body">
            {{ $slot }}
        </div>
    </div>
</div>
