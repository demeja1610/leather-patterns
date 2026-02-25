@props(['value' => false])

<span {{ $attributes->merge(['class' => 'bool-reverse' . ((bool) $value === false ? ' bool-reverse--negative' : null)]) }}>
    <span class="bool-reverse__dot"></span>

    <span class="bool-reverse__content">
        {{ $slot }}
    </span>
</span>
