<header {{ $attributes->merge(['class' => 'header']) }}>
    <div class="header__content">
        <a
            href="{{ route('admin.page.index.dashboard') }}"
            class="header__logo"
        >
            <x-icon.svg name="leather" />

            Leather patterns
        </a>

        <x-theme-toggler.theme-toggler />

        <x-header.auth />
    </div>
</header>
