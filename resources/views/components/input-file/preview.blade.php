@props([
    'errorMessages' => [],
    'label' => null,
    'required' => false,
    'placeholder' => __('phrases.select_file'),
    'files' => '[]',
    'multiple' => false,
    'accept' => 'application/pdf, image/vnd.dwg, application/zip, application/x-rar, application/x-7z-compressed, image/svg+xml, image/jpeg, image/png',
    'url',
    'id',
    'name',
])

@php
    if ($multiple === true) {
        $name = trim($name, '[]') . '[]';

        if ($placeholder === __('pselect_filee')) {
            $placeholder = __('phrases.select_files');
        }
    } else {
        $name = trim($name, '[]');
    }

    $removeName = "remove_{$name}";
@endphp

<div
    {{ $attributes->merge(['class' => 'preview-input-file' . ($required ? ' preview-input-file--required' : '')]) }}
    :class="{ 'preview-input-file--loading': loading }"
    x-data="previewInputFile()"
    data-name="{{ $name }}"
    data-url="{{ $url }}"
    data-files="{{ $files }}"
>
    <template
        x-for="(file, index) in getRemoveFiles()"
        :key="index"
    >
        <input
            type="hidden"
            name="{{ $removeName }}"
            x-bind:value="file.url"
        >
    </template>

    <template x-if="getFiles().length > 0">
        <div
            class="preview-input-file__previews"
            x-ref="previews"
        >
            <template
                x-for="(file, index) in getFiles()"
                :key="index"
            >
                <div class="preview-input-file__preview">
                    <template x-if="!file.id">
                        <input
                            type="hidden"
                            name="{{ $name }}"
                            x-bind:value="file.url"
                        >
                    </template>

                    <x-icon.svg
                        name="file"
                        class="preview-input-file__preview-icon"
                    />

                    <span
                        class="preview-input-file__preview-ext"
                        x-text="`.${file.ext}`"
                    ></span>

                    <button
                        x-on:click.prevent="removeFile(file);"
                        class="preview-input-file__remove"
                        title="{{ __('phrases.remove') }}"
                    >
                        <x-icon.svg
                            name="close"
                            class="preview-input-file__remove-icon"
                        />
                    </button>

                    <a
                        :href="file.url"
                        class="preview-input-file__download"
                        download
                    >
                        <x-icon.svg
                            name="download"
                            class="preview-input-file__download-icon"
                        />
                    </a>
                </div>
            </template>
        </div>
    </template>

    <input
        type="file"
        accept="{{ $accept }}"
        class="preview-input-file__input"
        id="{{ $id }}"
        @if ($multiple === true) multiple @endif
        x-on:change="uploadFiles()"
        tabindex="-1"
        x-ref="input"
    >

    <label
        class="preview-input-file__label"
        for="{{ $id }}"
        tabindex="0"
        x-on:keyup.space="$refs.input.click()"
    >
        @if ($label)
            <span class="preview-input-file__label-text {{ $required ? 'required' : '' }}">
                {{ $label }}
            </span>
        @endif

        <span class="preview-input-file__input-wrapper">
            <x-icon.svg
                name="file"
                class="preview-input-file__input-icon"
            />

            @if ($placeholder)
                <span class="preview-input-file__input-text">
                    {{ $placeholder }}
                </span>
            @endif

            <div class="preview-input-file__loading">
                <x-icon.svg
                    name="loading"
                    class="preview-input-file__loading-icon"
                />

                <span class="preview-input-file__loading-text">
                    {{ __('phrases.loading') }}
                </span>
            </div>
        </span>
    </label>

    <template x-if="getErrors().length > 0">
        <ul class="preview-input-file__errors">
            <template x-for="error in errors">
                <li
                    class="preview-input-file__error"
                    x-text="error"
                ></li>
            </template>
        </ul>
    </template>

    @php
        $_name = trim($name, '[]');
    @endphp

    @if ($errors->has("{$_name}.*"))
        <ul class="preview-input-file__errors">
            @foreach ($errors->get("{$_name}.*") as $fieldErrors)
                @foreach ($fieldErrors as $error)
                    <li class="preview-input-file__error">{{ $error }}</li>
                @endforeach
            @endforeach
        </ul>
    @endif
</div>
