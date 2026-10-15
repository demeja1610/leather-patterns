@props([
    'triggerTitle' => __('phrases.menu'),
])

<div {{ $attributes->merge(['class' => 'context-menu']) }} data-context-menu>
    <x-button.ghost
        :title="$triggerTitle"
        class="context-menu__trigger"
        data-context-menu-trigger
    >
        <x-icon.svg name="three-dots" />
    </x-button.ghost>

    <ul class="context-menu__items">
        {{ $slot }}
    </ul>
</div>
