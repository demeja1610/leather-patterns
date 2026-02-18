<form {{ $attributes->merge(['method' => 'POST', 'class' => 'admin-form-create']) }}>
    @csrf

    {{ $slot }}
</form>
