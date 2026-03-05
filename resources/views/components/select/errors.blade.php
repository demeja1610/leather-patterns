@props(['messages'])

@if ($messages && !empty($messages))
    <ul {{ $attributes->merge(['class' => 'select__errors']) }}>
        @foreach ((array) $messages as $message)
            <li class="select__error">{{ $message }}</li>
        @endforeach
    </ul>
@endif
