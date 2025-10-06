<x-filter.filter-item
    :title="$title ?? null"
    :class="'filter-item--search ' . ($class ?? '')"
>
    <x-input-text.input-text>
        <x-input-text.input
            :placeholder="$placeholder ?? __('filter.search_placeholder')"
            :name="$name ?? null"
            :value="$s ?? ''"
            class="filter-item__search-input"
            :title="$title ?? __('filter.search_placeholder')"
        />
    </x-input-text.input-text>
</x-filter.filter-item>
