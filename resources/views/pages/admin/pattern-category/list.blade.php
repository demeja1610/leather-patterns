@extends('layouts.admin')

@section('content')
    <div
        class="admin-page"
        x-data="{ deleteUrl: null }"
        x-on:close-modal="deleteUrl = null"
    >
        <x-admin.page-header.header
            title="{{ __('pattern_category.pattern_categories') }}"
            actionsUrl="{{ route('admin.pattern-category.mass-action') }}"
        >
            <x-link.button-default :href="route('admin.pattern-category.create')">
                {{ __('actions.add_new') }}
            </x-link.button-default>

            <x-slot:actions>
                <x-select.select
                    name="action"
                    id="action"
                    title="{{ __('actions.mass_actions') }}"
                >
                    <x-select.option value="">
                        {{ __('actions.mass_action') }}:{{ __('actions.not_selected') }}
                    </x-select.option>

                    <x-select.option value="delete">
                        {{ __('actions.mass_action') }}:{{ __('actions.delete') }}
                    </x-select.option>
                </x-select.select>
            </x-slot:actions>
        </x-admin.page-header.header>

        <form
            action="{{ route('admin.page.pattern-category.list') }}"
            method="POST"
            class="form"
        >
            @csrf

            <x-table.table x-data="{ all_checked: false }">
                <x-slot:header>
                    <x-table.head>
                        <x-table.th>
                            <x-checkbox.custom>
                                <input
                                    type="checkbox"
                                    id="ids"
                                    x-on:change="all_checked = !all_checked"
                                >
                            </x-checkbox.custom>
                        </x-table.th>

                        <x-table.th>
                            {{ __('pattern_category.id') }}
                        </x-table.th>

                        <x-table.th>
                            {{ __('pattern_category.name') }}
                        </x-table.th>

                        <x-table.th>
                            {{ __('pattern_category.created_at') }}
                        </x-table.th>

                        <x-table.th-actions class="table__header--actions">
                            {{ __('actions.actions') }}
                        </x-table.th-actions>
                    </x-table.head>
                </x-slot:header>

                <x-slot:rows>
                    @foreach ($categories as $category)
                        <x-table.tr>
                            <x-table.td>
                                <x-checkbox.custom>
                                    <input
                                        type="checkbox"
                                        id="category_{{ $category->id }}"
                                        name="ids[]"
                                        value="{{ $category->id }}"
                                        x-bind:checked="all_checked"
                                    >
                                </x-checkbox.custom>
                            </x-table.td>

                            <x-table.td>
                                {{ $category->id }}
                            </x-table.td>

                            <x-table.td>
                                {{ $category->name }}
                            </x-table.td>

                            <x-table.td>
                                {{ $category->created_at->format('d.m.Y H:i') }}
                            </x-table.td>

                            <x-table.td-actions>
                                <x-link.button-ghost :href="route('admin.page.pattern-category.edit', ['id' => $category->id])">
                                    <x-icon.svg name="edit" />
                                </x-link.button-ghost>

                                @if ($category->patterns_count === 0)
                                    <x-link.button-default
                                        :href="route('admin.pattern-category.delete', ['id' => $category->id])"
                                        x-on:click.prevent="() => {deleteUrl=$el.href}"
                                    >
                                        <x-icon.svg name="delete" />
                                    </x-link.button-default>
                                @endif
                            </x-table.td-actions>
                        </x-table.tr>
                    @endforeach
                </x-slot:rows>
            </x-table.table>
        </form>

        @if ($categories->nextPageUrl() || $categories->previousPageUrl())
            <x-pagination.cursor
                :prevPageUrl="$categories->previousPageUrl()"
                :nextPageUrl="$categories->nextPageUrl()"
            />
        @endif

        <x-modal.modal
            :title="__('phrases.confirmation')"
            x-show="deleteUrl !== null"
        >
            <x-form.confirm
                x-on:cancel="$dispatch('close-modal')"
                :confirm-text="__('actions.delete_confirm')"
                x-bind:action="deleteUrl"
                :text="__('pattern_category.admin.confirm_delete_text')"
            >
                @method('DELETE')
            </x-form.confirm>
        </x-modal.modal>
    </div>
@endsection
