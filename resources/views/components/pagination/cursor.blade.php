@props([
    'prevPageUrl' => '#',
    'nextPageUrl' => '#',
])

<div {{ $attributes->merge(['class' => 'pagination']) }}>
    <div class="pagination__container">
        <x-link.button-ghost :href="$prevPageUrl" class="pagination__button pagination__button--prev">
            <x-icon.svg
                name="chevron-left"
                class="pagination__button-icon"
            />

            {{ __('pagination.prev') }}
        </x-link.button-ghost>

        <x-link.button-ghost :href="$nextPageUrl" class="pagination__button pagination__button--next">
            {{ __('pagination.next') }}

            <x-icon.svg
                name="chevron-right"
                class="pagination__button-icon"
            />
        </x-link.button-ghost>
    </div>
</div>
