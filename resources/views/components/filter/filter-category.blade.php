@props([
    'showAllCategories' => false,
    'selectedCategories' => [],
    'categories' => [],
])

<x-filter.filter-item
    :title="__('filter.filter_categories_title')"
    class="filter-item-category"
    data-filter
>
    <noscript>
        <input
            type="hidden"
            name="show_all_pattern_categories"
            value="1"
        >

        <p class="filter-item-category__no-search">
            {{ __('phrases.search_not_awailable_disabled_js') }}
        </p>
    </noscript>

    <x-filter.filter-search
        :placeholder="__('filter.filter_categories_search')"
        :name="null"
        data-filter-input
    />

    <ul class="filter-item-category__list">
        @foreach ($categories as $category)
            <li
                class="filter-item-category__list-item"
                data-filter-item
            >
                {{-- HTML used instead of x-checkbox.custom component for optimization purposes --}}
                {{-- Change in carefully, if you know what you're doing becase there can be thousands of components to render --}}
                <label
                    class="checkbox"
                    data-filter-text
                >
                    <input
                        type="checkbox"
                        name="category[]"
                        value="{{ $category->id }}"
                        @checked(in_array($category->id, $selectedCategories))
                    />

                    <span class="checkbox__custom"></span>

                    {{ $category->name }}
                </label>
            </li>
        @endforeach

        <template>
            <li
                class="filter-item-category__list-item"
                data-filter-item
            >
                <label
                    class="checkbox"
                    data-filter-text
                >
                    <input
                        type="checkbox"
                        name="category[]"
                        value=""
                    />

                    <span class="checkbox__custom"></span>
                </label>
            </li>
        </template>

        @if ($showAllCategories === false)
            <li class="filter-item-category__list-item">
                <button
                    class="button-link filter-item-category__load-more"
                    data-load-more
                >
                    <span class="text">{{ __('filter.show_all') }}</span>

                    <x-loader.spin class="dn" />
                </button>
            </li>
        @endif

        <li class="filter-item-category__list-item filter-item-category__list-item--empty">
            {{ __('phrases.empty') }}
        </li>
    </ul>
</x-filter.filter-item>
