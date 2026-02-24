@props(['clamp' => 1])

<td {{ $attributes->merge(['class' => 'table-data-clamp']) }}>
    <span
        class="table-data-clamp__text"
        style="-webkit-line-clamp: {{ $clamp }}"
    >
        {{ $slot }}
    </span>
</td>
