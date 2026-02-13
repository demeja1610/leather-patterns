@props([
    'title' => null,
])

<div {{ $attributes->merge(['class' => 'filter-item']) }}>
    @if ($title !== null)
        <h3 class="filter-item__title">
            {{ $title }}
        </h3>
    @endif

    <div class="filter-item__content">
        {{ $slot }}
    </div>
</div>
