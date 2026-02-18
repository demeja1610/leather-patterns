@props(['selected' => false])

<option
    {{ $attributes->merge(['class' => 'select__option']) }}
    @selected($selected)
>
    {{ $slot }}
</option>
