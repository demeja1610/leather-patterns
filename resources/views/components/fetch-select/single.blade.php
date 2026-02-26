@props([
    'errorMessages' => [],
    'label' => null,
    'required' => false,
    'placeholder' => null,
    'selectedItem' => null,
    'selectedItemOptionValueName' => null,
    'selectedItemOptionLabelName' => null,
    'id',
    'name',
    'url',
])

<div
    {{ $attributes->merge(['class' => 'fetch-select' . ($required ? ' fetch-select--required' : '')]) }}
    x-data="fetchSelect()"
    data-url="{{ $url }}"
    data-selected-item="{{ $selectedItem }}"
    data-selected-item-option-value-name="{{ $selectedItemOptionValueName }}"
    data-selected-item-option-label-name="{{ $selectedItemOptionLabelName }}"
    x-on:keyup.escape.window="open=false"
    x-on:keydown.down.prevent="$focus.next()"
    x-on:keydown.up.prevent="$focus.previous()"
    x-bind:class="{
        'fetch-select--empty': getItems().length === 0,
        'fetch-select--has-items': getItems().length > 0,
        'fetch-select--has-selected': selectedItem !== null
    }"
>
    @if ($label)
        <label
            class="fetch-select__label {{ $required ? 'required' : '' }}"
            for="{{ $id }}"
        >
            {{ $label }}
        </label>
    @endif

    <select
        id="{{ $id }}"
        name="{{ $name }}"
        class="fetch-select__select"
        @required($required)
    >
        <option value=""></option>

        <template x-for="item in getItems()">
            <option
                x-bind:selected="isItemSelected(item)"
                x-bind:value="{{ $selectedItemOptionValueName !== null ? 'item.' . $selectedItemOptionValueName : 'item' }}"
                x-text="{{ $selectedItemOptionLabelName !== null ? 'item.' . $selectedItemOptionLabelName : 'item' }}"
            ></option>
        </template>
    </select>

    <x-input-text.input
        type="text"
        :title="$placeholder"
        :placeholder="$placeholder"
        x-model="q"
        x-on:focus="() => {if(getItems().length !== 0) open=true}"
        x-on:click.outside="open = false"
        x-on:input.debounce.500ms="fetchItems()"
        class="fetch-select__input"
    />

    <ul
        x-show="open"
        class="fetch-select__options"
    >
        <template
            x-for="item in getItems()"
            x-bind:key="{{ $selectedItemOptionValueName !== null ? 'item.' . $selectedItemOptionValueName : 'item' }}"
        >
            <li
                x-on:click="setSelectedItem(item)"
                x-on:keyup.enter="setSelectedItem(item)"
                class="fetch-select__option"
                tabindex="0"
                x-bind:class="{ 'fetch-select__option--active': isItemSelected(item) }"
            >
                <span x-text="{{ $selectedItemOptionLabelName !== null ? 'item.' . $selectedItemOptionLabelName : 'item' }}"></span>

                <template x-if="isItemSelected(item)">
                    <x-icon.svg
                        name="check"
                        class="fetch-select__option-check"
                    >
                    </x-icon.svg>
                </template>
            </li>
        </template>

        <template x-if="getItems().length === 0 && !loading">
            <li class="fetch-select__option fetch-select__option--empty">
                {{ __('phrases.empty') }}
            </li>
        </template>

        <template x-if="loading">
            <li class="fetch-select__option fetch-select__option--loading">
                {{ __('phrases.loading') }}
            </li>
        </template>
    </ul>

    @if (!empty($messages))
        <ul {{ $attributes->merge(['class' => 'fetch-select__errors']) }}>
            @foreach ((array) $messages as $message)
                <li>{{ $message }}</li>
            @endforeach
        </ul>
    @endif
</div>
