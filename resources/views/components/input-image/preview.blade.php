@props([
    'errorMessages' => [],
    'label' => null,
    'required' => false,
    'placeholder' => __('phrases.select_image'),
    'images' => '[]',
    'multiple' => false,
    'accept' => 'image/png, image/jpeg',
    'url',
    'id',
    'name',
])

@php
    if ($multiple === true) {
        $name = trim($name, '[]') . '[]';

        if ($placeholder === __('phrases.select_image')) {
            $placeholder = __('phrases.select_images');
        }
    } else {
        $name = trim($name, '[]');
    }

    $removeName = "remove_{$name}";
@endphp

<div
    {{ $attributes->merge(['class' => 'preview-input-image' . ($required ? ' preview-input-image--required' : '')]) }}
    :class="{ 'preview-input-image--loading': loading }"
    x-data="previewInputImage()"
    data-name="{{ $name }}"
    data-url="{{ $url }}"
    data-images="{{ $images }}"
>
    <template
        x-for="(image, index) in getRemoveImages()"
        :key="index"
    >
        <input
            type="hidden"
            name="{{ $removeName }}"
            x-bind:value="image.url"
        >
    </template>

    <template x-if="getImages().length > 0">
        <div
            class="preview-input-image__previews"
            x-ref="previews"
        >
            <template
                x-for="(image, index) in getImages()"
                :key="index"
            >
                <div class="preview-input-image__preview">
                    <template x-if="image.isNew === true">
                        <input
                            type="hidden"
                            name="{{ $name }}"
                            x-bind:value="image.url"
                        >
                    </template>

                    <a
                        x-bind:href="image.url"
                        x-on:click.prevent="openLightbox(index)"
                        class="preview-input-image__preview-link"
                    >
                        <img
                            x-bind:src="image.url"
                            class="preview-input-image__preview-image"
                        />
                    </a>

                    <button
                        x-on:click.prevent="removeImage(image);"
                        class="preview-input-image__remove"
                        title="{{ __('phrases.remove') }}"
                    >
                        <x-icon.svg
                            name="close"
                            class="preview-input-image__remove-icon"
                        />
                    </button>

                    <a
                        :href="image.url"
                        class="preview-input-image__download"
                        download
                    >
                        <x-icon.svg
                            name="download"
                            class="preview-input-image__download-icon"
                        />
                    </a>
                </div>
            </template>
        </div>
    </template>

    <input
        type="file"
        accept="{{ $accept }}"
        class="preview-input-image__input"
        id="{{ $id }}"
        @if ($multiple === true) multiple @endif
        x-on:change="uploadImages()"
        tabindex="-1"
        x-ref="input"
    >

    <label
        class="preview-input-image__label"
        for="{{ $id }}"
        tabindex="0"
        x-on:keyup.space="$refs.input.click()"
    >
        @if ($label)
            <span class="preview-input-image__label-text {{ $required ? 'required' : '' }}">
                {{ $label }}
            </span>
        @endif

        <span class="preview-input-image__input-wrapper">
            <x-icon.svg
                name="image"
                class="preview-input-image__input-icon"
            />

            @if ($placeholder)
                <span class="preview-input-image__input-text">
                    {{ $placeholder }}
                </span>
            @endif

            <div class="preview-input-image__loading">
                <x-icon.svg
                    name="loading"
                    class="preview-input-image__loading-icon"
                />

                <span class="preview-input-image__loading-text">
                    {{ __('phrases.loading') }}
                </span>
            </div>
        </span>
    </label>

    <template x-if="getErrors().length > 0">
        <ul class="preview-input-image__errors">
            <template x-for="error in errors">
                <li
                    class="preview-input-image__error"
                    x-text="error"
                ></li>
            </template>
        </ul>
    </template>

    @php
        $_name = trim($name, '[]');
    @endphp

    @if ($errors->has("{$_name}.*"))
        <ul class="preview-input-image__errors">
            @foreach ($errors->get("{$_name}.*") as $fieldErrors)
                @foreach ($fieldErrors as $error)
                    <li class="preview-input-image__error">{{ $error }}</li>
                @endforeach
            @endforeach
        </ul>
    @endif
</div>
