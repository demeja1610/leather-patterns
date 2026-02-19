@props(['url' => '#', 'resetUrl' => '#'])

<x-sidebar {{ $attributes->merge(['class' => 'admin-sidebar-filters']) }}>
    <h2 class="admin-sidebar-filters__title">
        {{ __('filter.filters') }}
    </h2>

    <form
        action="{{ $url }}"
        method="GET"
        class="admin-sidebar-filters__form"
    >
        {{ $slot }}

        <x-button.default class="admin-sidebar-filters__submit">
            {{ __('filter.apply') }}
        </x-button.default>

        <x-link.button-ghost :href="$resetUrl" class="admin-sidebar-filters__reset">
            {{ __('filter.reset') }}
        </x-link.button-ghost>
    </form>
</x-sidebar>
