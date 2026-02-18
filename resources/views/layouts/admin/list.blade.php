@extends('layouts.admin.default')

@section('content')
    <div
        class="admin-page admin-page-list"
        x-data="{ showFilters: false }"
    >
        <x-admin.page-header.header
            class="admin-page-list__header"
            :title="$title ?? null"
        >
            @yield('header-content')

            <x-slot:actions>
                @yield('header-actions')

                @hasSection('page-filters')
                    <x-button.ghost
                        :class="'admin-page-list__filters-toggler' . ($showFilters === false ? null : ' admin-page-list__filters-toggler--has-active-filters')"
                        x-on:click="showFilters = !showFilters"
                        x-bind:class="showFilters ? 'admin-page-list__filters-toggler--active' : ''"
                        :title="__('filter.filters')"
                    >
                        <x-icon.svg name="filter" />

                        <span class="admin-page-list__filters-toggler-text">
                            {{ count($activeFilters) }}
                        </span>
                    </x-button.ghost>
                @endif
            </x-slot:actions>
        </x-admin.page-header.header>

        <div class="admin-page-list__content">
            @yield('page')

            @hasSection('page-filters')
                <x-admin.sidebar.filters
                    class="admin-page-list__filters"
                    :url="route('admin.page.pattern-category.list')"
                    x-cloak
                    x-show="showFilters"
                >
                    @yield('page-filters')
                </x-admin.sidebar.filters>
            @endif
        </div>

        @if ($paginator->nextPageUrl() || $paginator->previousPageUrl())
            <x-pagination.cursor
                class="admin-page-list__pagination"
                :prevPageUrl="$paginator->previousPageUrl()"
                :nextPageUrl="$paginator->nextPageUrl()"
            />
        @endif
    </div>
@endsection
