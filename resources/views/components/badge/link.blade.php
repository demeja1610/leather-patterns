@props([
    'text' => null,
])

<a {{ $attributes->merge(['class' => 'badge badge-link']) }}>
    @if ($text !== null)
        <span class="badge__text">
            {{ $text }}
        </span>
    @endif

    {{ $slot }}
</a>
