@props([
    'showAllTags' => false,
    'tagsLimit' => 10,
    'selectedTags' => [],
    'tags' => [],
])

<x-filter.filter-item
    :title="__('filter.filter_tags_title')"
    class="filter-item-tag"
    data-filter
>
    <noscript>
        <input
            type="hidden"
            name="show_all_pattern_tags"
            value="1"
        >

        <p class="filter-item-tag__no-search">
            {{ __('phrases.search_not_awailable_disabled_js') }}
        </p>
    </noscript>

    <x-filter.filter-search
        :placeholder="__('filter.filter_tags_search')"
        :name="null"
        data-filter-input
    />

    <ul class="filter-item-tag__list">
        @foreach ($tags as $tag)
            <li
                class="filter-item-tag__list-item"
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
                        name="tag[]"
                        value="{{ $tag->id }}"
                        @checked(in_array($tag->id, $selectedTags))
                    />

                    <span class="checkbox__custom"></span>

                    {{ $tag->name }}
                </label>
            </li>
        @endforeach

        <template>
            <li
                class="filter-item-tag__list-item"
                data-filter-item
            >
                <label
                    class="checkbox"
                    data-filter-text
                >
                    <input
                        type="checkbox"
                        name="tag[]"
                        value=""
                    />

                    <span class="checkbox__custom"></span>
                </label>
            </li>
        </template>

         @if ($showAllTags === false && count($tags) >= $tagsLimit)
            <li class="filter-item-tag__list-item">
                <button
                    class="button-link filter-item-tag__load-more"
                    data-load-more
                >
                    <span class="text">{{ __('filter.show_all') }}</span>

                    <x-loader.spin class="dn" />
                </button>
            </li>
        @endif

        <li class="filter-item-tag__list-item filter-item-tag__list-item--empty">
            {{ __('phrases.empty') }}
        </li>
    </ul>
</x-filter.filter-item>
