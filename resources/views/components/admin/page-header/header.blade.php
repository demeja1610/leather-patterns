@props([
    'title' => null,
    'actions' => null,
])

<header {{ $attributes->merge(['class' => 'admin-page-header']) }}>
    @if ($title !== null)
        <h1 class="admin-page-header__title">
            {{ $title }}
        </h1>
    @endif

    {{ $slot }}

    @if ($actions)
        <div class="admin-page-header__actions">
            {{ $actions }}
        </div>
    @endif
</header>
