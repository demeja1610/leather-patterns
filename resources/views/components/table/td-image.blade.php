@props([
    'image' => null,
    'alt' => null,
    'canZoom' => false,
])

<td {{ $attributes->merge(['class' => 'table-data-image']) }}>
    <div class="table-data-image__image-wrapper">
        @if ($image)
            @if ($canZoom === true)
                <a
                    href="{{ $image }}"
                    class="table-data-image__image-link"
                    target="_blank"
                    data-fslightbox
                >
                    <img
                        src="{{ $image }}"
                        alt="{{ $alt }}"
                        class="table-data-image__image"
                    >
                </a>
            @else
                <img
                    src="{{ $image }}"
                    alt="{{ $alt }}"
                    class="table-data-image__image"
                >
            @endif
        @else
            <x-icon.svg
                name="image-placeholder"
                class="table-data-image__image-placeholder"
            />
        @endif
    </div>
</td>
