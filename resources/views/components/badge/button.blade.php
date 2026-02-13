@props([
    'text' => null,
])

<button {{ $attributes->merge(['class' => 'badge badge-button']) }}>
    @if ($text !== null)
        <span class="badge__text">
            {{ $text }}
        </span>
    @endif

    {{ $slot }}
</button>
