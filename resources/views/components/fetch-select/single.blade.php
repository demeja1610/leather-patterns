@props([
    'errorMessages' => [],
    'label' => null,
    'required' => false,
    'placeholder' => null,
    'selectedKey' => null,
    'selectedValue' => null,
    'keyName' => 'id',
    'valueName' => 'name',
    'id',
    'name',
    'url',
])

<div
    {{ $attributes->merge(['class' => 'fetch-select']) }}
    x-data="fetchSelect()"
    data-url="{{ $url }}"
    data-selected-key="{{ $selectedKey }}"
    data-selected-value="{{ $selectedValue }}"
    data-key-name="{{ $keyName }}"
    data-value-name="{{ $valueName }}"
    x-on:keyup.escape.window="open=false"
    x-on:keydown.down.prevent="$focus.next()"
    x-on:keydown.up.prevent="$focus.previous()"
>
    @if ($label)
        <label
            class="fetch-select__label"
            for="{{ $id }}"
        >
            {{ $label }}
        </label>
    @endif

    <input
        type="hidden"
        x-bind:value="selectedKey"
        id="{{ $id }}"
        name="{{ $name }}"
    >

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
            x-bind:key="item.{{ $keyName }}"
        >
            <li
                x-on:click="selectItem(item)"
                x-on:keyup.enter="selectItem(item)"
                x-text="item.{{ $valueName }}"
                class="fetch-select__option"
                tabindex="0"
            ></li>
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
