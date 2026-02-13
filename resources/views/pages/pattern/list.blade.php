@extends('layouts.app')

@php
    $filters = request()->all();
@endphp

@section('content')
    <form
        action="{{ route('page.index') }}"
        method="get"
        class="page page-pattern-list"
    >
        <x-filter.filter :resetUrl="route('page.index')">
            <x-filter.filter-category
                :categories="$categories"
                :selectedCategories="$filters['category'] ?? []"
                :showAllCategories="isset($filters['show_all_pattern_categories'])"
            />

            <x-filter.filter-tag
                :tags="$tags"
                :selectedTags="$filters['tag'] ?? []"
                :showAllTags="isset($filters['show_all_pattern_tags'])"
            />

            <x-filter.filter-author
                :authors="$authors"
                :selectedAuthors="$filters['author'] ?? []"
                :showAllAuthors="isset($filters['show_all_pattern_authors'])"
            />

            <x-filter.other-filters>
                <x-checkbox.custom :label="__('filter.filter_with_video_title')">
                    <input
                        type="checkbox"
                        class="checkbox__input"
                        name="has_video"
                        value="1"
                        @checked(isset($filters['has_video']))
                    />
                </x-checkbox.custom>

                <x-checkbox.custom :label="__('filter.filter_with_reviews_title')">
                    <input
                        type="checkbox"
                        class="checkbox__input"
                        name="has_review"
                        value="1"
                        @checked(isset($filters['has_review']))
                    />
                </x-checkbox.custom>

                <x-checkbox.custom :label="__('filter.filter_with_author_title')">
                    <input
                        type="checkbox"
                        class="checkbox__input"
                        name="has_author"
                        value="1"
                        @checked(isset($filters['has_author']))
                    />
                </x-checkbox.custom>
            </x-filter.other-filters>
        </x-filter.filter>

        <div class="page__content">
            <div class="page-pattern-list__search">
                <x-filter.filter-search
                    :name="'s'"
                    :s="$filters['s'] ?? null"
                />

                <x-select.wrapper>
                    <x-select.select
                        name="order"
                        id="order"
                        :title="__('filter.sort')"
                    >
                        <x-select.option value="">
                            {{ __('filter.sort') }}: {{ __('filter.pattern_order.default') }}
                        </x-select.option>

                        @foreach ($patternOrders as $patternOrder)
                            <x-select.option
                                :value="$patternOrder->value"
                                :selected="isset($filters['order']) && $filters['order'] === $patternOrder->value"
                            >
                                {{ __('filter.sort') }}: {{ __("filter.pattern_order.{$patternOrder->value}") }}
                            </x-select.option>
                        @endforeach
                    </x-select.select>
                </x-select.wrapper>

                <x-button.default :title="__('filter.search_placeholder')">
                    <x-icon.svg name="search" />
                </x-button.default>
            </div>

            <x-pattern.list :patterns="$patterns" />

            @if ($patterns->previousPageUrl() || $patterns->nextPageUrl())
                <x-pagination.cursor
                    :prevPageUrl="$patterns->previousPageUrl()"
                    :nextPageUrl="$patterns->nextPageUrl()"
                />
            @endif
        </div>
    </form>
@endsection
