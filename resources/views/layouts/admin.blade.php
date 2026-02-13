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
        <x-admin.sidebar.sidebar />

        <div class="admin-layout__content">
            <x-page-notifications.notifications :notifications="session()->get('notifications')" />

            @yield('content')
        </div>
    </div>
</body>

</html>
