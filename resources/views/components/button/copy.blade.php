@props([
    'copyValue' => 'copy',
])

<div {{ $attributes->merge(['x-data' => '{ copied: false }', 'class' => 'button-copy']) }}>
    <input
        type="hidden"
        x-ref="contentToCopy"
        value="{{ $copyValue }}"
        class="button-copy__input"
    >

    <button
        class="button-copy__button"
        @click.prevent="navigator.clipboard.writeText($refs.contentToCopy.value); copied = true; setTimeout(() => copied = false, 1200)"
    >
        <x-icon.svg
            name="copy"
            x-show="!copied"
            class="button-copy__icon button-copy__icon--copy"
        />

        <x-icon.svg
            name="check"
            x-cloak
            x-show="copied"
            class="button-copy__icon button-copy__icon--copied"
        />
    </button>
</div>
