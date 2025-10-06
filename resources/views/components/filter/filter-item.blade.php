<div class="filter-item {{ $class ?? '' }}">
    @if (!empty($title))
        <h3 class="filter-item__title">
            {{ $title }}
        </h3>
    @endif

    <div class="filter-item__content">
        {{ $slot }}
    </div>
</div>
