@props([
    'placeholder' => __('filter.search_placeholder'),
    'name' => null,
    's' => null,
])

<x-filter.filter-item {{ $attributes->merge(['class' => 'filter-item-search']) }}>
    <x-input-text.input-text class="filter-item-search__input">
        <x-input-text.input
            :placeholder="$placeholder"
            :name="$name"
            :value="$s"
            :title="$placeholder"
        />
    </x-input-text.input-text>
</x-filter.filter-item>
