<div
    {{ $attributes->merge(['class' => 'modal modal--hidden']) }}
    x-transition.duration.300ms
    x-on:keyup.escape.window="$dispatch('close-modal')"
    x-init="() => { setTimeout(() => { $el.classList.remove('modal--hidden') }, 300) }"
>
    <div
        class="modal__overlay"
        x-on:click.self="$dispatch('close-modal')"
    ></div>

    <div class="modal__content">
        <header class="modal__header">
            <h3 class="modal__title">
                {{ $title }}
            </h3>

            <button
                class="modal__close"
                x-on:click.prevent="$dispatch('close-modal')"
            ></button>
        </header>

        <div class="modal__body">
            {{ $slot }}
        </div>
    </div>
</div>
