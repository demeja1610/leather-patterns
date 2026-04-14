@extends('layouts.admin.list', [
    'paginator' => $files,
    'title' => __('pattern_file.files'),
    'showFilters' => $activeFilters !== [],
    'filterUrl' => route('admin.page.pattern-files.list'),
    'resetUrl' => route('admin.page.pattern-files.list'),
    'classes' => 'admin-page-pattern-files',
])

@section('page-filters')
    <x-input-text.input-text>
        <x-input-text.label for="id">
            {{ __('filter.id') }}
        </x-input-text.label>

        <x-input-text.input
            id="id"
            name="id"
            type="number"
            :placeholder="__('filter.id')"
            :value="$activeFilters['id'] ?? null"
            :title="__('filter.id')"
        />
    </x-input-text.input-text>

    <x-input-text.input-text>
        <x-input-text.label for="hash">
            {{ __('filter.hash') }}
        </x-input-text.label>

        <x-input-text.input
            id="hash"
            name="hash"
            type="text"
            :placeholder="__('filter.hash')"
            :value="$activeFilters['hash'] ?? null"
            :title="__('filter.hash')"
        />
    </x-input-text.input-text>

    <x-select.wrapper>
        <x-select.label for="type">
            {{ __('filter.type') }}
        </x-select.label>

        <x-select.select
            name="type"
            id="type"
            :title="__('filter.type')"
        >
            <x-select.option
                value=""
                :selected="!isset($activeFilters['type'])"
            >
                {{ __('filter.not_selected') }}
            </x-select.option>

            @foreach ($types as $type)
                <x-select.option
                    :value="$type"
                    :selected="isset($activeFilters['type']) && $activeFilters['type'] === $type"
                >
                    {{ __("pattern_file.types.{$type}") }}
                </x-select.option>
            @endforeach
        </x-select.select>
    </x-select.wrapper>

    <x-select.wrapper>
        <x-select.label for="ext">
            {{ __('filter.ext') }}
        </x-select.label>

        <x-select.select
            name="ext"
            id="ext"
            :title="__('filter.ext')"
        >
            <x-select.option
                value=""
                :selected="!isset($activeFilters['ext'])"
            >
                {{ __('filter.not_selected') }}
            </x-select.option>

            @foreach ($exts as $ext)
                <x-select.option
                    :value="$ext"
                    :selected="isset($activeFilters['ext']) && $activeFilters['ext'] === $ext"
                >
                    {{ ".{$ext}" }}
                </x-select.option>
            @endforeach
        </x-select.select>
    </x-select.wrapper>

    <x-select.wrapper>
        <x-select.label for="mime_type">
            {{ __('filter.mime_type') }}
        </x-select.label>

        <x-select.select
            name="mime_type"
            id="mime_type"
            :title="__('filter.mime_type')"
        >
            <x-select.option
                value=""
                :selected="!isset($activeFilters['mime_type'])"
            >
                {{ __('filter.not_selected') }}
            </x-select.option>

            @foreach ($mimeTypes as $mimeType)
                <x-select.option
                    :value="$mimeType"
                    :selected="isset($activeFilters['mime_type']) && $activeFilters['mime_type'] === $mimeType"
                >
                    {{ $mimeType }}
                </x-select.option>
            @endforeach
        </x-select.select>
    </x-select.wrapper>

    <x-select.wrapper>
        <x-select.label for="hash_algo">
            {{ __('filter.hash_algo') }}
        </x-select.label>

        <x-select.select
            name="hash_algo"
            id="hash_algo"
            :title="__('filter.hash_algo')"
        >
            <x-select.option
                value=""
                :selected="!isset($activeFilters['hash_algo'])"
            >
                {{ __('filter.not_selected') }}
            </x-select.option>

            @foreach ($hashAlgos as $hashAlgo)
                <x-select.option
                    :value="$hashAlgo"
                    :selected="isset($activeFilters['hash_algo']) && $activeFilters['hash_algo'] === $hashAlgo"
                >
                    {{ $hashAlgo }}
                </x-select.option>
            @endforeach
        </x-select.select>
    </x-select.wrapper>

    <x-fetch-select.single
        :url="route('api.admin.v1.pattern.search')"
        id="pattern_id"
        name="pattern_id"
        :label="__('pattern_file.pattern')"
        :placeholder="__('phrases.search')"
        selectedItemOptionValueName="id"
        selectedItemOptionLabelName="title"
        :selectedItem="isset($extraData['selected_pattern']) ? $extraData['selected_pattern']->toJson(JSON_UNESCAPED_UNICODE) : null"
    />

    <x-input-text.input-text>
        <x-input-text.label for="older_than">
            {{ __('filter.older_than') }}
        </x-input-text.label>

        <x-input-text.input
            id="older_than"
            name="older_than"
            type="datetime-local"
            :value="isset($activeFilters['older_than']) ? $activeFilters['older_than']->format('Y-m-d\\TH:i:s') : null"
            :title="__('filter.older_than')"
        />
    </x-input-text.input-text>

    <x-input-text.input-text>
        <x-input-text.label for="newer_than">
            {{ __('filter.newer_than') }}
        </x-input-text.label>

        <x-input-text.input
            id="newer_than"
            name="newer_than"
            type="datetime-local"
            :value="isset($activeFilters['newer_than']) ? $activeFilters['newer_than']->format('Y-m-d\\TH:i:s') : null"
            :title="__('filter.newer_than')"
        />
    </x-input-text.input-text>

    <x-select.wrapper>
        <x-select.label for="order_by">
            {{ __('filter.order_by') }}
        </x-select.label>

        <x-select.select
            name="order_by"
            id="order_by"
            :title="__('filter.order_by')"
        >
            <x-select.option
                value=""
                :selected="!isset($activeFilters['order_by'])"
            >
                {{ __('filter.not_selected') }}
            </x-select.option>

            @foreach ($orders as $order)
                <x-select.option
                    :value="$order"
                    :selected="isset($activeFilters['order_by']) && $activeFilters['order_by'] === $order"
                >
                    {{ __("filter.orders.{$order}") }}
                </x-select.option>
            @endforeach
        </x-select.select>
    </x-select.wrapper>

    <x-select.wrapper>
        <x-select.label for="order_direction">
            {{ __('filter.order_direction') }}
        </x-select.label>

        <x-select.select
            name="order_direction"
            id="order_direction"
            :title="__('filter.order_direction')"
        >
            <x-select.option
                value=""
                :selected="!isset($activeFilters['order_direction'])"
            >
                {{ __('filter.not_selected') }}
            </x-select.option>

            @foreach ($orderDirections as $orderDirection)
                <x-select.option
                    :value="$orderDirection"
                    :selected="isset($activeFilters['order_direction']) && $activeFilters['order_direction'] === $orderDirection"
                >
                    {{ __("filter.order_directions.{$orderDirection}") }}
                </x-select.option>
            @endforeach
        </x-select.select>
    </x-select.wrapper>
@endsection

@section('page')
    @if ($files->isEmpty())
        <x-table.empty>
            {{ __('phrases.nothing_found') }}
        </x-table.empty>
    @else
        <x-table.overflow-x-container
            x-data="{ deleteUrl: null }"
            x-on:close-modal="deleteUrl = null"
        >
            <x-table.table>
                <x-slot:header>
                    <x-table.head>
                        <x-table.th-actions class="table__header--actions">
                            {{ __('actions.actions') }}
                        </x-table.th-actions>

                        <x-table.th>
                            {{ __('pattern_file.id') }}
                        </x-table.th>

                        <x-table.th>
                            {{ __('pattern_file.hash') }}
                        </x-table.th>

                        <x-table.th>
                            {{ __('pattern_file.public_pattern_links') }}
                        </x-table.th>

                        <x-table.th>
                            {{ __('pattern_file.admin_pattern_links') }}
                        </x-table.th>

                        <x-table.th>
                            {{ __('pattern_file.download') }}
                        </x-table.th>

                        <x-table.th>
                            {{ __('pattern.images') }}
                        </x-table.th>

                        <x-table.th>
                            {{ __('pattern_file.type') }}
                        </x-table.th>

                        <x-table.th>
                            {{ __('pattern_file.ext') }}
                        </x-table.th>

                        <x-table.th>
                            {{ __('pattern_file.mb_size') }}
                        </x-table.th>

                        <x-table.th>
                            {{ __('pattern_file.mime_type') }}
                        </x-table.th>

                        <x-table.th>
                            {{ __('pattern_file.hash_algo') }}
                        </x-table.th>

                        <x-table.th>
                            {{ __('pattern_file.pattern_id') }}
                        </x-table.th>
                    </x-table.head>
                </x-slot:header>

                <x-slot:rows>
                    @foreach ($files as $file)
                        @php
                            $fileImages = [];

                            if ($file->pattern->images->isEmpty() === false) {
                                foreach ($file->pattern->images as $image) {
                                    $fileImages[] = asset('/storage/' . $image->path);
                                }
                            }
                        @endphp
                        <x-table.tr>
                            <x-table.td-actions>
                                @if ($file->isDeletable() === true)
                                    <x-link.button-default
                                        :href="route('admin.pattern-files.delete', ['id' => $file->id])"
                                        x-on:click.prevent="() => {deleteUrl=$el.href}"
                                    >
                                        <x-icon.svg name="delete" />
                                    </x-link.button-default>
                                @endif
                            </x-table.td-actions>

                            <x-table.td>
                                {{ $file->id }}
                            </x-table.td>

                            <x-table.td>
                                <x-button.copy :copyValue="$file->hash" />
                            </x-table.td>

                            <x-table.td>
                                <x-link.button-ghost
                                    :href="route('page.pattern.single', ['id' => $file->pattern->id])"
                                    target="_blank"
                                >
                                    {{ $file->pattern->id }}

                                    <x-icon.svg name="external-link" />
                                </x-link.button-ghost>
                            </x-table.td>

                            <x-table.td>
                                <x-link.button-default
                                    :href="route('admin.page.patterns.list', ['id' => $file->pattern->id])"
                                    target="_blank"
                                >
                                    {{ $file->pattern->id }}

                                    <x-icon.svg name="external-link" />
                                </x-link.button-default>
                            </x-table.td>

                            <x-table.td>
                                <x-link.button-default
                                    :href="asset('/storage/' . $file->path)"
                                    download
                                >
                                    <x-icon.svg name="download" />
                                </x-link.button-default>
                            </x-table.td>

                            <x-table.td-images
                                :images="$fileImages"
                                :canZoom="true"
                            />

                            <x-table.td>
                                {{ __("pattern_file.types.{$file->type->value}") }}
                            </x-table.td>

                            <x-table.td>
                                {{ ".{$file->extension}" }}
                            </x-table.td>

                            <x-table.td>
                                {{ round($file->size / 1024 / 1024, 3) }}

                                {{ __('pattern_file.mb') }}
                            </x-table.td>

                            <x-table.td>
                                {{ $file->mime_type }}
                            </x-table.td>

                            <x-table.td>
                                {{ $file->hash_algorithm }}
                            </x-table.td>

                            <x-table.td>
                                {{ $file->pattern_id }}
                            </x-table.td>
                        </x-table.tr>
                    @endforeach
                </x-slot:rows>
            </x-table.table>

            <x-modal.modal
                :title="__('phrases.confirmation')"
                x-show="deleteUrl !== null"
            >
                <x-form.confirm
                    x-on:cancel="$dispatch('close-modal')"
                    x-on:submit="setTimeout(() => $dispatch('close-modal'), 300)"
                    :confirm-text="__('actions.delete_confirm')"
                    x-bind:action="deleteUrl"
                    :text="__('pattern_file.admin.confirm_delete_text')"
                    x-trap="deleteUrl !== null"
                >
                    @method('DELETE')
                </x-form.confirm>
            </x-modal.modal>
        </x-table.overflow-x-container>
    @endif
@endsection
