@props([
    'text' => null,
])

<span {{ $attributes->merge(['class' => 'badge']) }}>
    {{ $slot }}
</span>
