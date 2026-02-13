@props([
    'title' => null,
    'actions' => null,
    'actionsUrl' => '#',
])

<header class="admin-page-header">
    <div class="admin-page-header__title-area">
        @if ($title !== null)
            <h1 class="admin-page-header__title">
                {{ $title }}
            </h1>
        @endif

        {{ $slot }}
    </div>

    @if ($actions !== null)
        <form
            action="{{ $actionsUrl }}"
            class="admin-page-header__actions"
        >
            @csrf

            {{ $actions }}
        </form>
    @endif
</header>
