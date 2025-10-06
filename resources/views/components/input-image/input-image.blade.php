@props(['disabled' => false, 'label' => null, 'messages' => []])


<div
    class="input-image"
    x-data="{ text: 'Image not selected' }"
>
    @if ($label)
        <label class="input-image__label">
            {{ $label }}
        </label>
    @endif

    <label class="input-image__wrapper">
        <span
            class="input-image__text"
            x-text="text"
        ></span>

        <input
            type="file"
            accept="image/jpeg, image/png, image/jpg"
            {{ $disabled ? 'disabled' : '' }}
            {!! $attributes->merge([
                'class' => 'input-image__input',
            ]) !!}
            x-on:change="text = $event.target.files[0].name"
        >

        <span class="input-image__btn">
            Select image
        </span>
    </label>

    @if ($messages && !empty($messages))
        <ul class="input-image__errors">
            @foreach ($messages as $message)
                <li class="input-image__error">{{ $message }}</li>
            @endforeach
        </ul>
    @endif
</div>
