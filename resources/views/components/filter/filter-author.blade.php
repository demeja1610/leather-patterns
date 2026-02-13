@props([
    'showAllAuthors' => false,
    'selectedAuthors' => [],
    'authors' => [],
])

<x-filter.filter-item
    :title="__('filter.filter_authors_title')"
    class="filter-item-author"
    data-filter
>
    <noscript>
        <input
            type="hidden"
            name="show_all_pattern_authors"
            value="1"
        >

        <p class="filter-item-author__no-search">
            {{ __('phrases.search_not_awailable_disabled_js') }}
        </p>
    </noscript>

    <x-filter.filter-search
        :placeholder="__('filter.filter_authors_search')"
        :name="null"
        data-filter-input
    />

    <ul class="filter-item-author__list">
        @foreach ($authors as $author)
            <li
                class="filter-item-author__list-item"
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
                        name="author[]"
                        value="{{ $author->id }}"
                        @checked(in_array($author->id, $selectedAuthors))
                    />

                    <span class="checkbox__custom"></span>

                    {{ $author->name }}
                </label>
            </li>
        @endforeach

        <template>
            <li
                class="filter-item-author__list-item"
                data-filter-item
            >
                <label
                    class="checkbox"
                    data-filter-text
                >
                    <input
                        type="checkbox"
                        name="author[]"
                        value=""
                    />

                    <span class="checkbox__custom"></span>
                </label>
            </li>
        </template>

        @if ($showAllAuthors === false)
            <li class="filter-item-author__list-item">
                <button
                    class="button-link filter-item-author__load-more"
                    data-load-more
                >
                    <span class="text">{{ __('filter.show_all') }}</span>

                    <x-loader.spin class="dn" />
                </button>
            </li>
        @endif

        <li class="filter-item-author__list-item filter-item-author__list-item--empty">
            {{ __('phrases.empty') }}
        </li>
    </ul>
</x-filter.filter-item>
