@props(['paginator'])

@php
    $window = \Illuminate\Pagination\UrlWindow::make($paginator);

    $elements = array_filter([$window['first'], is_array($window['slider']) ? '...' : null, $window['slider'], is_array($window['last']) ? '...' : null, $window['last']]);
@endphp

@if ($paginator->hasPages())
    <div {{ $attributes->merge(['class' => 'pagination']) }}>
        <div class="pagination__container">
            <x-link.button-ghost
                :href="$paginator->previousPageUrl() ?? '#'"
                class="pagination__button pagination__button--prev"
            >
                <x-icon.svg
                    name="chevron-left"
                    class="pagination__button-icon"
                />

                {{ __('pagination.prev') }}
            </x-link.button-ghost>

            @foreach ($elements as $element)
                @if (is_string($element))
                    <x-link.button-ghost
                        href="#"
                        class="pagination__button"
                    >
                        {{ $element }}
                    </x-link.button-ghost>
                @endif

                @if (is_array($element))
                    @foreach ($element as $page => $url)
                        @if ($page == $paginator->currentPage())
                            <x-link.button-default
                                href="#"
                                class="pagination__button pagination__button--current"
                            >
                                {{ $page }}
                            </x-link.button-default>
                        @else
                            <x-link.button-ghost
                                :href="$url"
                                class="pagination__button"
                            >
                                {{ $page }}
                            </x-link.button-ghost>
                        @endif
                    @endforeach
                @endif
            @endforeach

            <x-link.button-ghost
                :href="$paginator->nextPageUrl() ?? '#'"
                class="pagination__button pagination__button--next"
            >
                {{ __('pagination.next') }}

                <x-icon.svg
                    name="chevron-right"
                    class="pagination__button-icon"
                />
            </x-link.button-ghost>
        </div>
    </div>
@endif
