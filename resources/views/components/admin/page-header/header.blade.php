@props([
    'title' => null,
    'actions' => null,
])

<header class="admin-page-header">
    <h1 class="admin-page-header__title">
        {{ $title }}
    </h1>

    {{ $slot }}

    @if ($actions)
        <div class="admin-page-header__actions">
            {{ $actions }}
        </div>
    @endif
</header>
