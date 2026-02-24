@props(['required' => false])

<input
    {{ $attributes->merge([
        'type' => 'text',
        'class' => 'input-text__input',
    ]) }}
    @required($required)
>
