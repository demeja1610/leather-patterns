@props([
    'download' => false,
])
<a
    {{ $attributes->merge(['class' => 'link-button-default']) }}

    @if ($download !== false) download @endif
>
    {{ $slot }}
</a>
