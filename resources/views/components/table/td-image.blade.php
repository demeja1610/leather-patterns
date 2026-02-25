@props([
    'image' => null,
    'alt' => null,
])

<td {{ $attributes->merge(['class' => 'table-data-image']) }}>
    <div class="table-data-image__image-wrapper">
        @if ($image)
            <img
                src="{{ $image }}"
                alt="{{ $alt }}"
                class="table-data-image__image"
            >
        @else
            <x-icon.svg
                name="image-placeholder"
                class="table-data-image__image-placeholder"
            />
        @endif
    </div>
</td>
