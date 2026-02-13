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

            <x-link.default
                href="{{ route('page.auth.forgot-password') }}"
                title="{{ __('auth.sign_up') }}"
                class="login-form__forgot-password"
            >
                {{ __('auth.forgot_password') }}
            </x-link.default>
        </x-input-text.label>

        <x-input-text.input
            id="password"
            name="password"
            type="password"
            :title="__('auth.password')"
        />

        <x-input-text.input-errors :messages="$errors->get('password')" />
    </x-input-text.input-text>

    <x-button.default :title="__('auth.login')">
        {{ __('auth.login') }}
    </x-button.default>

    <p class="login-form__signup">
        {{ __('auth.dont_have_account') }}

        <x-link.default
            :href="route('page.auth.register')"
            :title="__('auth.sign_up')"
        >
            {{ __('auth.sign_up') }}
        </x-link.default>
    </p>
</form>
