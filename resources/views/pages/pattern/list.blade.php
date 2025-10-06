@extends('layouts.app')

@section('content')
    <form
        action="{{ route('page.index') }}"
        method="get"
        class="page page--index"
    >
        <x-filter.filter :resetUrl="route('page.index')">
           <x-filter.filter-category
                :categories="$categories"
                :activeCategories="$activeCategoriesIds"
            />

             <x-filter.filter-tag
                :tags="$tags"
                :activeTags="$activeTagsIds"
            />

            <x-filter.filter-author
                :authors="$authors"
                :activeAuthors="$activeAuthorsIds"
            />

            <x-filter.other-filters>
                <x-filter.filter-video :checked="$hasVideo" />

                <x-filter.filter-review :checked="$hasReview" />
            </x-filter.other-filters>
        </x-filter.filter>

        <div class="page__content">
            <div class="page__search">
                <x-filter.filter-search
                    :class="'page__filter-item-search'"
                    :name="'s'"
                    :s="$search"
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
                                :selected="$order === $patternOrder->value"
                            >
                                {{ __('filter.sort') }}: {{ __("filter.pattern_order.{$patternOrder->value}") }}
                            </x-select.option>
                        @endforeach
                    </x-select.select>
                </x-select.wrapper>

                <button
                    type="submit"
                    class="button button--primary"
                    title="{{ __('filter.search_placeholder') }}"
                >
                    <x-icon.svg name="search" />
                </button>
            </div>

            <div class="patterns">
                @foreach ($patterns as $pattern)
                    <x-pattern.list-item :pattern="$pattern" />
                @endforeach
            </div>

            @if ($patterns->previousPageUrl() || $patterns->nextPageUrl())
                <x-pagination.cursor
                    :prevPageUrl="$patterns->previousPageUrl()"
                    :nextPageUrl="$patterns->nextPageUrl()"
                />
            @endif
        </div>
    </form>
@endsection
