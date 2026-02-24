@props(['messages'])

@if ($messages && !empty($messages))
    <ul {{ $attributes->merge(['class' => 'textarea__errors']) }}>
        @foreach ((array) $messages as $message)
            <li class="textarea__error">{{ $message }}</li>
        @endforeach
    </ul>
@endif
