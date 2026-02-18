<form {{ $attributes->merge(['method' => 'POST', 'class' => 'admin-form-edit']) }}>
    @csrf

    {{ $slot }}
</form>
