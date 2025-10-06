@props(['prevPageUrl', 'nextPageUrl'])

<div class="pagination">
    <div class="pagination__container">
        <a
            href="{{ $prevPageUrl ?? '#' }}"
            class="pagination__button pagination__button--prev"
        >
            <x-icon.svg
                name="chevron-left"
                class="pagination__icon"
            />

            {{ __('pagination.prev') }}
        </a>

        <a
            href="{{ $nextPageUrl ?? '#' }}"
            class="pagination__button pagination__button--next"
        >
            {{ __('pagination.next') }}

            <x-icon.svg
                name="chevron-right"
                class="pagination__icon"
            />
        </a>
    </div>
</div>
