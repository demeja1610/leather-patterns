@props([
    'patterns' => [],
])

<div class="patterns">
    @foreach ($patterns as $pattern)
        <x-pattern.list-item :pattern="$pattern" />
    @endforeach
</div>
