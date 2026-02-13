<div class="header-auth">
    @guest
        <x-link.button-default
            :href="route('page.auth.login')"
            :title="__('auth.sign_in')"
        >
            {{ __('auth.sign_in') }}
        </x-link.button-default>
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
                {{ auth()->user()->name }}
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
