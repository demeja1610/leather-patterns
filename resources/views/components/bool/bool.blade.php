@props(['value' => false])

<span {{ $attributes->merge(['class' => 'bool' . ((bool) $value === false ? ' bool--negative' : null)]) }}>
    <span class="bool__dot"></span>

    <span class="bool__content">
        {{ $slot }}
    </span>
</span>
