<form
    method="POST"
    action="{{ route('auth.register') }}"
    class="form register-form"
>
    @csrf

    <x-input-text.input-text>
        <x-input-text.label for="name">
            {{ __('auth.name') }}
        </x-input-text.label>

        <x-input-text.input
            type="text"
            name="name"
            id="name"
            :value="old('name')"
            :title="__('auth.name')"
        />

        <x-input-text.input-errors :messages="$errors->get('name')" />
    </x-input-text.input-text>

    <x-input-text.input-text>
        <x-input-text.label for="email">
            {{ __('auth.email') }}
        </x-input-text.label>

        <x-input-text.input
            type="email"
            name="email"
            id="email"
            :value="old('email')"
            :title="__('auth.email')"
        />

        <x-input-text.input-errors :messages="$errors->get('email')" />
    </x-input-text.input-text>

    <x-input-text.input-text>
        <x-input-text.label for="password">
            {{ __('auth.password') }}
        </x-input-text.label>

        <x-input-text.input
            type="password"
            name="password"
            id="password"
            :title="__('auth.password')"
            autocomplete="new-password"
        />

        <x-input-text.input-errors :messages="$errors->get('password')" />
    </x-input-text.input-text>

    <x-input-text.input-text>
        <x-input-text.label for="password_confirmation">
            {{ __('auth.password_confirmation') }}
        </x-input-text.label>

        <x-input-text.input
            type="password"
            name="password_confirmation"
            id="password_confirmation"
            :title="__('auth.password_confirmation')"
        />
    </x-input-text.input-text>

    <button
        class="button register-form__submit"
        type="submit"
        :title="__('auth.register')"
    >
        {{ __('auth.register') }}
    </button>

    <p class="register-form__signup">
        {{ __('auth.already_have_account') }}

        <a
            class="link register-form__signup-link"
            href="{{ route('page.auth.login') }}"
            :title="__('auth.sign_in')"
        >
            {{ __('auth.sign_in') }}
        </a>
    </p>
</form>
