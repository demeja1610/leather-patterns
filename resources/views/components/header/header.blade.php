<header class="header">
    <div class="header__content">
        <a
            href="{{ route('page.index') }}"
            class="header__logo"
        >
            <x-icon.svg name="leather" />

            Leather patterns
        </a>

        <x-theme-toggler.theme-toggler />

        <x-header.auth />
    </div>
</header>
