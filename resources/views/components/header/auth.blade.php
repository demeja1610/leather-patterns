<div class="header-auth">
    @guest
        <a
            class="button header-auth__login"
            href="{{ route('page.auth.login') }}"
        >
            Sign in
        </a>
    @endguest

    @auth
        <a
            class="header-auth__dashboard-link"
            href="#"
        >
            <x-icon.svg
                name="user"
                class="header-auth__dashboard-link-icon"
            />

            <span class="header-auth__dashboard-link-username">
                {{ Auth::user()->name }}
            </span>
        </a>
        <form
            action="{{ route('auth.logout') }}"
            method="POST"
        >
            @csrf

            <button
                type="submit"
                class="header-auth__logout"
            >
                <x-icon.svg
                    name="logout"
                    class="header-auth__logout-icon"
                />
            </button>
        </form>
    @endauth
</div>
