<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
    <meta charset="utf-8">
    <meta
        name="viewport"
        content="width=device-width, initial-scale=1"
    >

    <title>{{ $title ?? config('app.name') }}</title>

    @vite(['resources/assets/scss/admin.scss', 'resources/assets/js/admin.js'])

    <script>
        if (
            localStorage.getItem("theme") === "dark" ||
            (!localStorage.getItem("theme") &&
                window.matchMedia("(prefers-color-scheme: dark)").matches)
        ) {
            document.documentElement.classList.add("dark");
        }
    </script>
</head>

<body>
    <x-icon._sprite-admin />

    <x-header.admin />

    <div class="admin-layout">
        <x-sidebar-menu.menu>
            @php
                $currentRoute = request()->route()->getName();
                $menu = config('admin_menu');
            @endphp

            @foreach ($menu as $item)
                @if (isset($item['items']))
                    <x-sidebar-menu.list
                        :headerText="$item['label'] ?? null"
                        :url="isset($item['route']) ? route($item['route']) : null"
                        :isActive="isset($item['route']) && $item['route'] === $currentRoute ? true : false"
                        :icon="$item['icon'] ?? null"
                    >
                        @foreach ($item['items'] as $subItem)
                            @if (isset($subItem['route']))
                                <x-sidebar-menu.item-link
                                    :url="route($subItem['route'])"
                                    :isActive="$subItem['route'] === $currentRoute ? true : false"
                                    :icon="$subItem['icon'] ?? null"
                                    :label="$subItem['label'] ?? null"
                                />
                            @else
                                <x-sidebar-menu.item-text
                                    :icon="$subItem['icon'] ?? null"
                                    :label="$subItem['label'] ?? null"
                                />
                            @endif
                        @endforeach
                    </x-sidebar-menu.list>
                @else
                    @if (isset($item['route']))
                        <x-sidebar-menu.item-link
                            :url="route($item['route'])"
                            :isActive="$item['route'] === $currentRoute ? true : false"
                            :icon="$item['icon'] ?? null"
                            :label="$item['label'] ?? null"
                        />
                    @else
                        <x-sidebar-menu.item-text
                            :icon="$item['icon'] ?? null"
                            :label="$item['label'] ?? null"
                        />
                    @endif
                @endif
            @endforeach

        </x-sidebar-menu.menu>

        <div class="admin-layout__content">
            @php
                $session = session();

                $pageInfoNotification = $session->get('page-info-notification');
                $pageWarnNotification = $session->get('page-warning-notification');
                $pageErrorNotification = $session->get('page-error-notification');
                $pageSuccessNotification = $session->get('page-success-notification');
            @endphp

            <x-page-notifications.wrapper>
                @if ($pageInfoNotification !== null)
                    <x-page-notifications.item :text="$pageInfoNotification" />
                @endif

                @if ($pageWarnNotification !== null)
                    <x-page-notifications.item
                        :text="$pageWarnNotification"
                        type="warning"
                    />
                @endif

                @if ($pageErrorNotification !== null)
                    <x-page-notifications.item
                        :text="$pageErrorNotification"
                        type="error"
                    />
                @endif

                @if ($pageSuccessNotification !== null)
                    <x-page-notifications.item
                        :text="$pageSuccessNotification"
                        type="success"
                    />
                @endif
            </x-page-notifications.wrapper>

            @yield('content')
        </div>
    </div>
</body>

</html>
