@props(['url', 'isActive' => false, 'icon' => null, 'label' => null])

<a
    href="{{ $url }}"
    class="sidebar-menu__item sidebar-menu__item--link {{ $isActive ? 'sidebar-menu__item--active' : '' }}"
>
    @if (isset($icon) && $icon !== null)
        <svg
            width="16"
            height="16"
            class="sidebar-menu__item-icon"
            fill="currentColor"
        >
            <use href="#icon-{{ $icon }}" />
        </svg>
    @endif

    @if (isset($label) && $label !== null)
        <span>{{ $label }}</span>
    @endif
</a>
