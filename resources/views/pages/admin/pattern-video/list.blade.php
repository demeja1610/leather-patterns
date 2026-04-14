@extends('layouts.admin.list', [
    'paginator' => $videos,
    'title' => __('pattern_video.pattern_videos'),
    'showFilters' => $activeFilters !== [],
    'filterUrl' => route('admin.page.pattern-videos.list'),
    'resetUrl' => route('admin.page.pattern-videos.list'),
    'classes' => 'admin-page-pattern-videos-list',
])

@section('header-content')
    <x-link.button-default :href="route('admin.pattern-videos.create')">
        <x-icon.svg name="create" />

        {{ __('actions.add_new') }}
    </x-link.button-default>
@endsection

@section('page-filters')
    <x-input-text.input-text>
        <x-input-text.label for="id">
            {{ __('filter.id') }}
        </x-input-text.label>

        <x-input-text.input
            id="id"
            name="id"
            type="text"
            :placeholder="__('filter.id')"
            :value="$activeFilters['id'] ?? null"
            :title="__('filter.id')"
        />
    </x-input-text.input-text>

    <x-input-text.input-text>
        <x-input-text.label for="url">
            {{ __('filter.url') }}
        </x-input-text.label>

        <x-input-text.input
            id="url"
            name="url"
            type="url"
            :placeholder="__('filter.url')"
            :value="$activeFilters['url'] ?? null"
            :title="__('filter.url')"
        />
    </x-input-text.input-text>

    <x-select.wrapper>
        <x-select.label for="source">
            {{ __('filter.source') }}
        </x-select.label>

        <x-select.select
            name="source"
            id="source"
            :title="__('pattern.source')"
        >
            <x-select.option
                value=""
                :selected="!isset($activeFilters['source'])"
            >
                {{ __('filter.not_selected') }}
            </x-select.option>

            @foreach ($sources as $source)
                <x-select.option
                    :value="$source"
                    :selected="isset($activeFilters['source']) && $activeFilters['source'] === $source"
                >
                    {{ __("pattern_video.sources.{$source}") }}
                </x-select.option>
            @endforeach
        </x-select.select>
    </x-select.wrapper>

    <x-input-text.input-text>
        <x-input-text.label for="source_identifier">
            {{ __('filter.source_identifier') }}
        </x-input-text.label>

        <x-input-text.input
            id="source_identifier"
            name="source_identifier"
            type="text"
            :placeholder="__('filter.source_identifier')"
            :value="$activeFilters['source_identifier'] ?? null"
            :title="__('filter.source_identifier')"
        />
    </x-input-text.input-text>

    <x-select.wrapper>
        <x-select.label for="has_patterns">
            {{ __('pattern_video.has_patterns') }}
        </x-select.label>

        <x-select.select
            name="has_patterns"
            id="has_patterns"
            :title="__('pattern_video.has_patterns')"
        >
            <x-select.option
                value=""
                :selected="!isset($activeFilters['has_patterns'])"
            >
                {{ __('filter.not_selected') }}
            </x-select.option>

            <x-select.option
                value="1"
                :selected="isset($activeFilters['has_patterns']) && $activeFilters['has_patterns'] === true"
            >
                {{ __('phrases.yes') }}
            </x-select.option>

            <x-select.option
                value="0"
                :selected="isset($activeFilters['has_patterns']) && $activeFilters['has_patterns'] === false"
            >
                {{ __('phrases.no') }}
            </x-select.option>

        </x-select.select>
    </x-select.wrapper>

    <x-fetch-select.single
        :url="route('api.admin.v1.pattern.search')"
        id="pattern_id"
        name="pattern_id"
        :label="__('pattern_video.pattern')"
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
@endsection

@section('page')
    @if ($videos->isEmpty())
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
                            {{ __('pattern_video.id') }}
                        </x-table.th>

                        <x-table.th>
                            {{ __('pattern_video.url') }}
                        </x-table.th>

                        <x-table.th>
                            {{ __('pattern_video.pattern') }}
                        </x-table.th>

                        <x-table.th>
                            {{ __('pattern_video.source') }}
                        </x-table.th>

                        <x-table.th>
                            {{ __('pattern_video.source_identifier') }}
                        </x-table.th>

                        <x-table.th>
                            {{ __('pattern_video.created_at') }}
                        </x-table.th>
                    </x-table.head>
                </x-slot:header>

                <x-slot:rows>
                    @foreach ($videos as $video)
                        <x-table.tr>
                            <x-table.td-actions>
                                @if ($video->isDeletable() === true)
                                    <x-link.button-default
                                        :href="route('admin.pattern-videos.delete', ['id' => $video->id])"
                                        x-on:click.prevent="() => {deleteUrl=$el.href}"
                                    >
                                        <x-icon.svg name="delete" />
                                    </x-link.button-default>
                                @endif

                                <x-link.button-ghost :href="route('admin.page.pattern-videos.edit', ['id' => $video->id])">
                                    <x-icon.svg name="edit" />
                                </x-link.button-ghost>
                            </x-table.td-actions>

                            <x-table.td>
                                {{ $video->id }}
                            </x-table.td>

                            <x-table.td>
                                <x-link.default
                                    :href="$video->url"
                                    target="_blank"
                                >
                                    <x-icon.svg name="external-link" />
                                </x-link.default>
                            </x-table.td>

                            <x-table.td>
                                <x-link.default
                                    :href="route('admin.page.patterns.edit', ['id' => $video->pattern->id])"
                                    target="_blank"
                                >
                                    {{ $video->pattern->title }}
                                </x-link.default>
                            </x-table.td>

                            <x-table.td>
                                {{ __("pattern_video.sources.{$video->source->value}") }}
                            </x-table.td>

                            <x-table.td>
                                {{ $video->source_identifier }}
                            </x-table.td>

                            <x-table.td>
                                {{ $video->created_at->translatedFormat('d F Y H:i') }}
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
                    :text="__('pattern_author.admin.confirm_delete_text')"
                >
                    @method('DELETE')
                </x-form.confirm>
            </x-modal.modal>
        </x-table.overflow-x-container>
    @endif
@endsection
