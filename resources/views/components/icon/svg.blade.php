@props(['name'])

<svg
    {{ $attributes->merge(['class' => "icon icon--{$name}"]) }}
    width="16"
    height="16"
    fill="currentColor"
>
    <use xlink:href="#icon-{{ $name }}"></use>
</svg>
