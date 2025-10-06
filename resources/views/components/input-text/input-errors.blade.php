@props(['messages'])

@if ($messages && !empty($messages))
    <ul {{ $attributes->merge(['class' => 'input-text__errors']) }}>
        @foreach ((array) $messages as $message)
            <li class="input-text__error">{{ $message }}</li>
        @endforeach
    </ul>
@endif
