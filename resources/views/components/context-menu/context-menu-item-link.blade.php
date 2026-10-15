<li class="context-menu__item">
    <a {{ $attributes->merge(['class' => 'context-menu__item-link']) }}>
        {{ $slot }}
    </a>
</li>
