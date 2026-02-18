@props(['value' => false])

<td {{ $attributes->merge(['class' => 'table-data-bool' . ((bool) $value === false ? ' table-data-bool--negative' : null)]) }}>
    <div class="table-data-bool__wrapper">
        <span class="table-data-bool__dot"></span>

        <span class="table-data-bool__content">
            {{ $slot }}
        </span>
    </div>
</td>
