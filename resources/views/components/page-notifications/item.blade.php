@props([
    'text' => '',
    'type' => '',
])

<div class="page-notifications__item page-notifications__item--{{ $type }}">
    <p class="page-notifications__item-text">
        {{ $text }}
    </p>

    <button
        class="page-notifications__item-remove"
        onclick="this.closest('div').remove()"
    ></button>
</div>
