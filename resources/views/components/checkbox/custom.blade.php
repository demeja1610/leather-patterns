@props(['label' => null])

<label {{ $attributes->merge(['class' => 'checkbox']) }}>
    {{ $slot }}

    <span class="checkbox__custom"></span>

    {{ $label }}
</label>
