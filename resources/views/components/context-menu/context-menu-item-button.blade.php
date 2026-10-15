<li class="context-menu__item">
    <button {{ $attributes->merge(['class' => 'context-menu__item-button']) }}>
        {{ $slot }}
    </button>
</li>
