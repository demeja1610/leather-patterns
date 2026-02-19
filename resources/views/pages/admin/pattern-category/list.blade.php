@extends('layouts.admin.list', [
    'paginator' => $categories,
    'title' => __('pattern_category.pattern_categories'),
    'showFilters' => $activeFilters !== [],
])

@section('header-content')
    <x-link.button-default :href="route('admin.pattern-category.create')">
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
        <x-input-text.label for="name">
            {{ __('filter.name') }}
        </x-input-text.label>

        <x-input-text.input
            id="name"
            name="name"
            type="text"
            :value="$activeFilters['name'] ?? null"
            :title="__('filter.name')"
        />
    </x-input-text.input-text>

    <x-select.wrapper>
        <x-select.label for="has_patterns">
            {{ __('pattern_category.has_patterns') }}
        </x-select.label>

        <x-select.select
            name="has_patterns"
            id="has_patterns"
            :title="__('pattern_category.has_patterns')"
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
            {{ __('pattern_category.is_published') }}
        </x-select.label>

        <x-select.select
            name="is_published"
            id="is_published"
            :title="__('pattern_category.is_published')"
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

    <x-select.wrapper>
        <x-select.label for="has_replacement">
            {{ __('pattern_category.has_replacement') }}
        </x-select.label>

        <x-select.select
            name="has_replacement"
            id="has_replacement"
            :title="__('pattern_category.has_replacement')"
        >
            <x-select.option
                value=""
                :selected="!isset($activeFilters['has_replacement'])"
            >
                {{ __('filter.not_selected') }}
            </x-select.option>

            <x-select.option
                value="1"
                :selected="isset($activeFilters['has_replacement']) && $activeFilters['has_replacement'] === true"
            >
                {{ __('phrases.yes') }}
            </x-select.option>

            <x-select.option
                value="0"
                :selected="isset($activeFilters['has_replacement']) && $activeFilters['has_replacement'] === false"
            >
                {{ __('phrases.no') }}
            </x-select.option>

        </x-select.select>
    </x-select.wrapper>

    <x-select.wrapper>
        <x-select.label for="remove_on_appear">
            {{ __('pattern_category.remove_on_appear') }}
        </x-select.label>

        <x-select.select
            name="remove_on_appear"
            id="remove_on_appear"
            :title="__('pattern_category.remove_on_appear')"
        >
            <x-select.option
                value=""
                :selected="!isset($activeFilters['remove_on_appear'])"
            >
                {{ __('filter.not_selected') }}
            </x-select.option>

            <x-select.option
                value="1"
                :selected="isset($activeFilters['remove_on_appear']) && $activeFilters['remove_on_appear'] === true"
            >
                {{ __('phrases.yes') }}
            </x-select.option>

            <x-select.option
                value="0"
                :selected="isset($activeFilters['remove_on_appear']) && $activeFilters['remove_on_appear'] === false"
            >
                {{ __('phrases.no') }}
            </x-select.option>

        </x-select.select>
    </x-select.wrapper>
@endsection

@section('page')
    @if ($categories->isEmpty())
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
                            {{ __('pattern_category.id') }}
                        </x-table.th>

                        <x-table.th>
                            {{ __('pattern_category.name') }}
                        </x-table.th>

                        <x-table.th>
                            {{ __('pattern_category.is_published') }}
                        </x-table.th>

                        <x-table.th>
                            {{ __('pattern_category.patterns_count') }}
                        </x-table.th>

                        <x-table.th>
                            {{ __('pattern_category.replacement') }}
                        </x-table.th>

                        <x-table.th>
                            {{ __('pattern_category.remove_on_appear') }}
                        </x-table.th>

                        <x-table.th>
                            {{ __('pattern_category.created_at') }}
                        </x-table.th>
                    </x-table.head>
                </x-slot:header>

                <x-slot:rows>
                    @foreach ($categories as $category)
                        <x-table.tr>
                            <x-table.td-actions>
                                @if ($category->patterns_count === 0)
                                    <x-link.button-default
                                        :href="route('admin.pattern-category.delete', ['id' => $category->id])"
                                        x-on:click.prevent="() => {deleteUrl=$el.href}"
                                    >
                                        <x-icon.svg name="delete" />
                                    </x-link.button-default>
                                @endif

                                <x-link.button-ghost :href="route('admin.page.pattern-category.edit', ['id' => $category->id])">
                                    <x-icon.svg name="edit" />
                                </x-link.button-ghost>


                            </x-table.td-actions>

                            <x-table.td>
                                {{ $category->id }}
                            </x-table.td>

                            <x-table.td>
                                {{ $category->name }}
                            </x-table.td>

                            <x-table.td-bool :value="$category->is_published">
                                {{ $category->is_published ? __('phrases.yes') : __('phrases.no') }}
                            </x-table.td-bool>

                            <x-table.td>
                                {{ $category->patterns_count }}
                            </x-table.td>

                            <x-table.td>
                                {{ $category->replacement?->name }}
                            </x-table.td>

                            <x-table.td-bool :value="$category->remove_on_appear">
                                {{ $category->remove_on_appear ? __('phrases.yes') : __('phrases.no') }}
                            </x-table.td-bool>

                            <x-table.td>
                                {{ $category->created_at->translatedFormat('d F Y H:i') }}
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
                    :text="__('pattern_category.admin.confirm_delete_text')"
                >
                    @method('DELETE')
                </x-form.confirm>
            </x-modal.modal>
        </x-table.overflow-x-container>
    @endif
@endsection
