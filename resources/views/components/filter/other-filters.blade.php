<x-filter.filter-item
    :title="__('filter.other_filters_title')"
    :class="'filter-item--other-filters'"
>
    <div class="filter-item__other-filters-list">
        {{ $slot }}
    </div>
</x-filter.filter-item>
