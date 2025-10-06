<form
    method="POST"
    action="{{ route('auth.login') }}"
    class="form login-form"
>
    @csrf

    @if (session('status'))
        <x-form.alert>
            {{ session('status') }}
        </x-form.alert>
    @endif

    <x-input-text.input-text>
        <x-input-text.label for="email">
            {{ __('auth.email') }}
        </x-input-text.label>

        <x-input-text.input
            id="email"
            name="email"
            type="email"
            :value="old('email')"
            :title="__('auth.email')"
        />

        <x-input-text.input-errors :messages="$errors->get('email')" />
    </x-input-text.input-text>

    <x-input-text.input-text>
        <x-input-text.label
            for="password"
            class="login-form__password-label"
        >
            {{ __('auth.password') }}

            <a
                class="link login-form__forgot-password"
                href="{{ route('page.forgot-password') }}"
            >
                {{ __('auth.forgot_password') }}
            </a>
        </x-input-text.label>

        <x-input-text.input
            id="password"
            name="password"
            type="password"
            :title="__('auth.password')"
        />

        <x-input-text.input-errors :messages="$errors->get('password')" />
    </x-input-text.input-text>

    <button
        class="button login-form__submit"
        type="submit"
        title="{{ __('auth.login') }}"
    >
        {{ __('auth.login') }}
    </button>

    <p class="login-form__signup">
        {{ __('auth.dont_have_account') }}

        <a
            class="link login-form__signup-link"
            href="{{ route('page.register') }}"
            title="{{ __('auth.sign_up') }}"
        >
            {{ __('auth.sign_up') }}
        </a>
    </p>
</form>
