@props(['headerText', 'url' => null, 'isActive' => false, 'icon' => null])

<div
    class="sidebar-menu__list"
    x-data="{ open: {{ isset($isActive) && $isActive === true ? 'true' : 'false' }} }"
>
    <div class="sidebar-menu__list-header">
        @if (isset($url) && $url !== null)
            <x-sidebar-menu.item-link
                :url="$url"
                :isActive="$isActive"
                :label="$headerText"
                :icon="$icon"
            />
        @else
            <x-sidebar-menu.item-text
                :label="$headerText"
                :icon="$icon"
            />
        @endif

        <button
            class="sidebar-menu__list-header-toggle {{ $isActive ? 'sidebar-menu__list-header-toggle--active' : '' }}"
            x-on:click="open = !open"
            :class="{ 'sidebar-menu__list-header-toggle--active': open }"
        >
            <svg
                width="16"
                height="16"
                class="sidebar-menu__list-header-toggle-icon"
                fill="currentColor"
            >
                <use href="#icon-chevron-down" />
            </svg>
        </button>
    </div>

    <div
        class="sidebar-menu__list-items {{ $isActive ? 'sidebar-menu__list-items--active' : '' }}"
        :class="{ 'sidebar-menu__list-items--active': open }"
    >
        {{ $slot }}
    </div>
</div>
