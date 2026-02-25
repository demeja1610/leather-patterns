@props(['headerText' => null])

<td {{ $attributes->merge([
    'class' => 'table-data-dropdown',
    'x-data' => '{open: false}',
]) }}>
    <div class="table-data-dropdown__header">
        @if ($headerText !== null)
            <p class="table-data-dropdown__title">
                {{ $headerText }}
            </p>
        @endif

        <x-button.ghost x-on:click="open = !open">
            <x-icon.svg
                name="chevron-down"
                class="table-data-dropdown__icon"
                x-bind:class="{ 'table-data-dropdown__icon--open': open }"
            />
        </x-button.ghost>
    </div>

    <div
        class="table-data-image__dropdown-content"
        x-show="open"
    >
        {{ $slot }}
    </div>
</td>
