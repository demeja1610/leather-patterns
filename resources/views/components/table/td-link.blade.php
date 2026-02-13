<td {{ $attributes->merge(['class' => 'table__data table__data--link']) }}>
    <a
        href="{{ $url }}"
        class="link table__data-link"
        target="_blank"
    >
        {{ $slot }}
    </a>
</td>
