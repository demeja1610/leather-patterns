@props([
    'errorMessages' => [],
    'label' => null,
    'required' => false,
    'placeholder' => null,
    'selectedItems' => '[]',
    'selectedItemOptionValueName' => null,
    'selectedItemOptionLabelName' => null,
    'id',
    'name',
    'url',
])

<div
    {{ $attributes->merge(['class' => 'multiple-fetch-select' . ($required ? ' multiple-fetch-select--required' : '')]) }}
    x-data="multipleFetchSelect()"
    data-url="{{ $url }}"
    data-selected-items="{{ $selectedItems }}"
    data-selected-item-option-value-name="{{ $selectedItemOptionValueName }}"
    data-selected-item-option-label-name="{{ $selectedItemOptionLabelName }}"
    x-on:keyup.escape.window="hideOptions()"
    x-on:keydown.down.prevent="$focus.next()"
    x-on:keydown.up.prevent="$focus.previous()"
    x-bind:class="{
        'multiple-fetch-select--empty': getItems().length === 0,
        'multiple-fetch-select--has-items': getItems().length > 0,
        'multiple-fetch-select--has-selected': getSelectedItems().length > 0
    }"
>
    @if ($label)
        <label
            class="multiple-fetch-select__label {{ $required ? 'required' : '' }}"
            for="{{ $id }}"
        >
            {{ $label }}
        </label>
    @endif

    <select
        id="{{ $id }}"
        name="{{ $name }}"
        class="multiple-fetch-select__select"
        multiple
        @required($required)
    >
        <option value=""></option>

        <template x-for="item in getSelectedItems()">
            <option
                x-bind:selected="isItemSelected(item)"
                x-bind:value="{{ $selectedItemOptionValueName !== null ? 'item.' . $selectedItemOptionValueName : 'item' }}"
                x-text="{{ $selectedItemOptionLabelName !== null ? 'item.' . $selectedItemOptionLabelName : 'item' }}"
            ></option>
        </template>
    </select>

    <template x-if="getSelectedItems().length > 0">
        <div class="multiple-fetch-select__selected-items">
            <template x-for="selectedItem in getSelectedItems()">
                <x-badge.badge class="multiple-fetch-select__selected-item">
                    <span
                        class="multiple-fetch-select__selected-item-text"
                        x-text="{{ $selectedItemOptionLabelName !== null ? 'selectedItem.' . $selectedItemOptionLabelName : 'selectedItem' }}"
                    ></span>

                    <button
                        class="multiple-fetch-select__selected-item-remove"
                        x-on:click.prevent="removeSelectedItem(selectedItem)"
                    >
                        <x-icon.svg
                            name="close"
                            class="multiple-fetch-select__selected-item-remove-icon"
                        />
                    </button>
                </x-badge.badge>
            </template>
        </div>
    </template>

    <div
        class="multiple-fetch-select__container"
        x-on:click.outside="hideOptions()"
    >
        <x-input-text.input
            type="text"
            :title="$placeholder"
            :placeholder="$placeholder"
            x-model="q"
            x-on:focus="() => {if(getItems().length !== 0) showOptions()}"
            x-on:input.debounce.500ms="fetchItems()"
            x-ref="textInput"
            class="multiple-fetch-select__input"
        />

        <ul
            x-show="shouldShowOptions()"
            class="multiple-fetch-select__options"
        >
            <template
                x-for="item in getItems()"
                x-bind:key="{{ $selectedItemOptionValueName !== null ? 'item.' . $selectedItemOptionValueName : 'item' }}"
            >
                <li
                    x-on:click="toggleSelectedItem(item)"
                    x-on:keyup.enter="toggleSelectedItem(item)"
                    class="multiple-fetch-select__option"
                    tabindex="0"
                    x-bind:class="{ 'multiple-fetch-select__option--active': isItemSelected(item) }"
                >
                    <span x-text="{{ $selectedItemOptionLabelName !== null ? 'item.' . $selectedItemOptionLabelName : 'item' }}"></span>

                    <template x-if="isItemSelected(item)">
                        <x-icon.svg
                            name="check"
                            class="multiple-fetch-select__option-check"
                        >
                        </x-icon.svg>
                    </template>
                </li>
            </template>

            <template x-if="getItems().length === 0 && !loading">
                <li class="multiple-fetch-select__option multiple-fetch-select__option--empty">
                    {{ __('phrases.empty') }}
                </li>
            </template>

            <template x-if="loading">
                <li class="multiple-fetch-select__option multiple-fetch-select__option--loading">
                    {{ __('phrases.loading') }}
                </li>
            </template>
        </ul>

        @if (!empty($messages))
            <ul {{ $attributes->merge(['class' => 'multiple-fetch-select__errors']) }}>
                @foreach ((array) $messages as $message)
                    <li>{{ $message }}</li>
                @endforeach
            </ul>
        @endif
    </div>
</div>
