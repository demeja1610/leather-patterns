<x-sidebar.sidebar>
    <x-sidebar.menu>
        @foreach ($menu as $menuItem)
            <x-sidebar.menu-item :menuItem="$menuItem" />
        @endforeach
    </x-sidebar.menu>
</x-sidebar.sidebar>
