@props([
    'images' => [],
    'canZoom' => false,
])

<td {{ $attributes->merge(['class' => 'table-data-images']) }}>
    <div class="table-data-images__image-wrapper">
        @if ($images === [])
            <x-icon.svg
                name="image-placeholder"
                class="table-data-images__image-placeholder"
            />
        @else
            @foreach ($images as $image)
                @if ($canZoom === true)
                    <a
                        href="{{ $image }}"
                        class="table-data-images__image-link"
                        target="_blank"
                        data-fslightbox
                    >
                        <img
                            src="{{ $image }}"
                            class="table-data-images__image"
                        >
                    </a>
                @else
                    <img
                        src="{{ $image }}"
                        class="table-data-images__image"
                    >
                @endif
            @endforeach
        @endif
    </div>
</td>
