@props(['resetUrl' => '#'])

<div class="filter">
    {{ $slot }}

    <x-button.default
        :title="__('filter.apply')"
        class="filter__submit"
    >
        {{ __('filter.apply') }}
    </x-button.default>

    <x-link.button-ghost
        :href="$resetUrl"
        :title="__('filter.reset')"
        class="filter__reset"
    >
        {{ __('filter.reset') }}
    </x-link.button-ghost>
</div>
