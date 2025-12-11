@props(['icon' => null, 'label' => null])

<p class="sidebar-menu__item sidebar-menu__item--text">
    @if(isset($icon) && $icon !== null)
        <svg
            width="16"
            height="16"
            class="sidebar-menu__item-icon"
            fill="currentColor"
        >
            <use href="#icon-{{ $icon }}" />
        </svg>
    @endif

    @if(isset($label) && $label !== null)
        <span>{{ $label }}</span>
    @endif
</p>
