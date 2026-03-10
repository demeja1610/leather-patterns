@extends('layouts.admin.list', [
    'paginator' => $socials,
    'title' => __('pattern_author_social.pattern_author_socials'),
    'showFilters' => $activeFilters !== [],
    'filterUrl' => route('admin.page.pattern-author-social.list'),
    'resetUrl' => route('admin.page.pattern-author-social.list'),
    'classes' => 'admin-page-pattern-author-social-list',
])

@section('header-content')
    <x-link.button-default :href="route('admin.pattern-author-social.create')">
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
            type="text"
            :value="$activeFilters['url'] ?? null"
            :title="__('filter.url')"
        />
    </x-input-text.input-text>

    <x-select.wrapper>
        <x-select.label for="type">
            {{ __('pattern.source') }}
        </x-select.label>

        <x-select.select
            name="type"
            id="type"
            :title="__('pattern_author_social.type')"
        >
            <x-select.option
                value=""
                :selected="!isset($activeFilters['type'])"
            >
                {{ __('filter.not_selected') }}
            </x-select.option>

            @foreach ($types as $type)
                <x-select.option
                    :value="$type->value"
                    :selected="isset($activeFilters['type']) && $activeFilters['type'] === $type"
                >
                    {{ __("pattern_author_social.types.{$type->value}") }}
                </x-select.option>
            @endforeach
        </x-select.select>
    </x-select.wrapper>

    <x-fetch-select.single
        :url="route('api.admin.v1.pattern-author.search')"
        id="author_id"
        name="author_id"
        :label="__('pattern.author')"
        :placeholder="__('phrases.search')"
        selectedItemOptionValueName="id"
        selectedItemOptionLabelName="name"
        :selectedItem="isset($extraData['selected_author']) ? $extraData['selected_author']->toJson(JSON_UNESCAPED_UNICODE) : null"
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
        <x-select.label for="is_published">
            {{ __('pattern_author.is_published') }}
        </x-select.label>

        <x-select.select
            name="is_published"
            id="is_published"
            :title="__('pattern_author.is_published')"
        >
            <x-select.option
                value=""
                :selected="!isset($activeFilters['is_published'])"
            >
                {{ __('filter.not_selected') }}
            </x-select.option>

            <x-select.option
                value="1"
                :selected="isset($activeFilters['is_published']) && $activeFilters['is_published'] === true"
            >
                {{ __('phrases.yes') }}
            </x-select.option>

            <x-select.option
                value="0"
                :selected="isset($activeFilters['is_published']) && $activeFilters['is_published'] === false"
            >
                {{ __('phrases.no') }}
            </x-select.option>

        </x-select.select>
    </x-select.wrapper>
@endsection

@section('page')
    @if ($socials->isEmpty())
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
                            {{ __('pattern_author_social.id') }}
                        </x-table.th>

                        <x-table.th>
                            {{ __('pattern_author_social.url') }}
                        </x-table.th>

                        <x-table.th>
                            {{ __('pattern_author_social.type') }}
                        </x-table.th>

                        <x-table.th>
                            {{ __('pattern_author_social.author') }}
                        </x-table.th>

                        <x-table.th>
                            {{ __('pattern_author.is_published') }}
                        </x-table.th>

                        <x-table.th>
                            {{ __('pattern_author.created_at') }}
                        </x-table.th>
                    </x-table.head>
                </x-slot:header>

                <x-slot:rows>
                    @foreach ($socials as $social)
                        <x-table.tr>
                            <x-table.td-actions>
                                @if ($social->isDeletable() === true)
                                    <x-link.button-default
                                        :href="route('admin.pattern-author-social.delete', ['id' => $social->id])"
                                        x-on:click.prevent="() => {deleteUrl=$el.href}"
                                    >
                                        <x-icon.svg name="delete" />
                                    </x-link.button-default>
                                @endif

                                <x-link.button-ghost :href="route('admin.page.pattern-author-social.edit', ['id' => $social->id])">
                                    <x-icon.svg name="edit" />
                                </x-link.button-ghost>
                            </x-table.td-actions>

                            <x-table.td>
                                {{ $social->id }}
                            </x-table.td>

                            <x-table.td>
                                <div class="admin-page-pattern-author-social-list__socials">
                                    <x-link.default
                                        :href="$social->url"
                                        target="_blank"
                                        class="admin-page-pattern-author-social-list__social"
                                    >
                                        <x-icon.svg
                                            :name="$social->type->value"
                                            class="admin-page-pattern-author-social-list__social-icon admin-page-pattern-author-social-list__social-icon--{{ $social->type->value }}"
                                        />
                                    </x-link.default>
                                </div>
                            </x-table.td>

                            <x-table.td>
                                {{ __("pattern_author_social.types.{$social->type->value}") }}
                            </x-table.td>

                            <x-table.td>
                                <x-link.default
                                    :href="route('admin.page.pattern-author.list', ['id' => $social->author->id])"
                                    target="_blank"
                                >
                                    {{ $social->author->name }}
                                </x-link.default>
                            </x-table.td>

                            <x-table.td-bool :value="$social->is_published">
                                {{ $social->is_published ? __('phrases.yes') : __('phrases.no') }}
                            </x-table.td-bool>

                            <x-table.td>
                                {{ $social->created_at->translatedFormat('d F Y H:i') }}
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
                    :text="__('pattern_author_social.admin.confirm_delete_text')"
                >
                    @method('DELETE')
                </x-form.confirm>
            </x-modal.modal>
        </x-table.overflow-x-container>
    @endif
@endsection
