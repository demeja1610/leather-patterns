@props(['open' => true])

<ul {{ $attributes->merge(['class' => 'sidebar-menu' . ($open === true ? ' sidebar-menu--active' : '')]) }}>
    {{ $slot }}
</ul>
