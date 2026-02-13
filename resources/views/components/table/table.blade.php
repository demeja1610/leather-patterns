<table {{ $attributes->merge(['class' => 'table']) }}>
    @isset($header)
        {{ $header }}
    @endisset

    <tbody class="table__body">
        @isset($rows)
            {{ $rows }}
        @endisset
    </tbody>

    @isset($footer)
        {{ $footer }}
    @endisset
</table>
