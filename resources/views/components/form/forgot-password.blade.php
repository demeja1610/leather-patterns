<form
    method="POST"
    action="{{ route('auth.forgot-password') }}"
    class="form forgot-password-form"
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

    <button
        class="button"
        type="submit"
    >
        {{ __('auth.forgot_password') }}
    </button>
</form>
