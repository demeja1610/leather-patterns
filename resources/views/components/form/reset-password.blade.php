<form
    method="POST"
    action="{{ route('auth.reset-password') }}"
    class="form reset-password-form"
>
    @csrf

    <input
        type="hidden"
        name="token"
        value="{{ $token }}"
    >

    <input
        type="hidden"
        name="email"
        value="{{ $email }}"
    >

    <x-input-text.input-text>
        <x-input-text.label for="password">
            {{ __('auth.password') }}
        </x-input-text.label>

        <x-input-text.input
            id="password"
            name="password"
            type="password"
        />

        <x-input-text.input-errors :messages="$errors->get('password')" />
    </x-input-text.input-text>

    <x-input-text.input-text>
        <x-input-text.label for="password_confirmation">
            {{ __('auth.password_confirmation') }}
        </x-input-text.label>

        <x-input-text.input
            id="password_confirmation"
            name="password_confirmation"
            type="password"
        />
    </x-input-text.input-text>

    <x-button.default :title="__('auth.reset_password')">
        {{ __('auth.reset_password') }}
    </x-button.default>
</form>
