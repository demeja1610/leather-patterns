@props(['menuItem'])

<li
    class="sidebar-menu__item {{ $menuItem->getSubMenu() !== null && $menuItem->getSubMenu()->isEmpty() === false ? 'sidebar-menu__item--list' : '' }}"
    x-data="{ open: {{ $menuItem->isActive() === true ? 'true' : 'false' }} }"
>
    <div class="sidebar-menu__item-header">
        @if ($menuItem->getDirectUrl() !== null || $menuItem->getRoute() !== null)
            <a
                href="{{ $menuItem->getDirectUrl() !== null ? $menuItem->getDirectUrl() : route($menuItem->getRoute()) }}"
                class="sidebar-menu__item-link {{ $menuItem->isActive() ? 'sidebar-menu__item-link--active' : '' }}"
            >
            @else
                <p class="sidebar-menu__item-info {{ $menuItem->isActive() ? 'sidebar-menu__item-info--active' : '' }}">
        @endif

        @if ($menuItem->getIcon() !== null)
            <svg
                width="16"
                height="16"
                class="sidebar-menu__item-icon"
                fill="currentColor"
            >
                <use href="#icon-{{ $menuItem->getIcon() }}" />
            </svg>
        @endif

        @if ($menuItem->getText() !== null)
            <span class="sidebar-menu__item-text">{{ $menuItem->getText() }}</span>
        @endif

        @if ($menuItem->getDirectUrl() !== null || $menuItem->getRoute() !== null)
            </a>
        @else
            </p>
        @endif

        @if ($menuItem->getSubMenu() !== null && $menuItem->getSubMenu()->isEmpty() === false)
            <button
                class="sidebar-menu__item-toggler {{ $menuItem->isActive() ? 'sidebar-menu__item-toggler--active' : '' }}"
                @click="open = !open"
                :class="{ 'sidebar-menu__item-toggler--active': open }"
            >
                <svg
                    width="16"
                    height="16"
                    class="sidebar-menu__item-toggler-icon"
                    fill="currentColor"
                >
                    <use href="#icon-chevron-down" />
                </svg>
            </button>
        @endif
    </div>

    @if ($menuItem->getSubMenu() !== null && $menuItem->getSubMenu()->isEmpty() === false)
        <x-sidebar.menu
            :open="$menuItem->isActive()"
            x-bind:class="{ 'sidebar-menu--active': open }"
        >
            @foreach ($menuItem->getSubMenu() as $menuItem)
                <x-sidebar.menu-item :menuItem="$menuItem" />
            @endforeach
        </x-sidebar.menu>
    @endif
</li>
