@extends('layouts.admin.list', [
    'paginator' => $reviews,
    'title' => __('pattern_review.pattern_reviews'),
    'showFilters' => $activeFilters !== [],
    'filterUrl' => route('admin.page.pattern-review.list'),
    'resetUrl' => route('admin.page.pattern-review.list'),
])

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
        <x-input-text.label for="reviewer_name">
            {{ __('pattern_review.reviewer_name') }}
        </x-input-text.label>

        <x-input-text.input
            id="reviewer_name"
            name="reviewer_name"
            type="text"
            :value="$activeFilters['reviewer_name'] ?? null"
            :title="__('pattern_review.reviewer_name')"
        />
    </x-input-text.input-text>

    <x-select.wrapper>
        <x-select.label for="has_rating">
            {{ __('pattern_review.has_rating') }}
        </x-select.label>

        <x-select.select
            name="has_rating"
            id="has_rating"
            :title="__('pattern_review.has_rating')"
        >
            <x-select.option
                value=""
                :selected="!isset($activeFilters['has_rating'])"
            >
                {{ __('filter.not_selected') }}
            </x-select.option>

            <x-select.option
                value="1"
                :selected="isset($activeFilters['has_rating']) && $activeFilters['has_rating'] === true"
            >
                {{ __('phrases.yes') }}
            </x-select.option>

            <x-select.option
                value="0"
                :selected="isset($activeFilters['has_rating']) && $activeFilters['has_rating'] === false"
            >
                {{ __('phrases.no') }}
            </x-select.option>
        </x-select.select>
    </x-select.wrapper>

    <x-input-text.input-text>
        <x-input-text.label for="rating_more_than">
            {{ __('pattern_review.rating_more_than') }}
        </x-input-text.label>

        <x-input-text.input
            id="rating_more_than"
            name="rating_more_than"
            type="number"
            :value="$activeFilters['rating_more_than'] ?? null"
            :title="__('pattern_review.rating_more_than')"
        />
    </x-input-text.input-text>

    <x-input-text.input-text>
        <x-input-text.label for="rating_less_than">
            {{ __('pattern_review.rating_less_than') }}
        </x-input-text.label>

        <x-input-text.input
            id="rating_less_than"
            name="rating_less_than"
            type="number"
            :value="$activeFilters['rating_less_than'] ?? null"
            :title="__('pattern_review.rating_less_than')"
        />
    </x-input-text.input-text>

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
        <x-select.label for="is_approved">
            {{ __('pattern_review.is_approved') }}
        </x-select.label>

        <x-select.select
            name="is_approved"
            id="is_approved"
            :title="__('pattern_review.is_approved')"
        >
            <x-select.option
                value=""
                :selected="!isset($activeFilters['is_approved'])"
            >
                {{ __('filter.not_selected') }}
            </x-select.option>

            <x-select.option
                value="1"
                :selected="isset($activeFilters['is_approved']) && $activeFilters['is_approved'] === true"
            >
                {{ __('phrases.yes') }}
            </x-select.option>

            <x-select.option
                value="0"
                :selected="isset($activeFilters['is_approved']) && $activeFilters['is_approved'] === false"
            >
                {{ __('phrases.no') }}
            </x-select.option>

        </x-select.select>
    </x-select.wrapper>

    <x-select.wrapper>
        <x-select.label for="has_user">
            {{ __('pattern_review.has_user') }}
        </x-select.label>

        <x-select.select
            name="has_user"
            id="has_user"
            :title="__('pattern_review.has_user')"
        >
            <x-select.option
                value=""
                :selected="!isset($activeFilters['has_user'])"
            >
                {{ __('filter.not_selected') }}
            </x-select.option>

            <x-select.option
                value="1"
                :selected="isset($activeFilters['has_user']) && $activeFilters['has_user'] === true"
            >
                {{ __('phrases.yes') }}
            </x-select.option>

            <x-select.option
                value="0"
                :selected="isset($activeFilters['has_user']) && $activeFilters['has_user'] === false"
            >
                {{ __('phrases.no') }}
            </x-select.option>
        </x-select.select>
    </x-select.wrapper>
@endsection

@section('page')
    @if ($reviews->isEmpty())
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
                            {{ __('pattern_review.id') }}
                        </x-table.th>

                        <x-table.th>
                            {{ __('pattern_review.reviewer_name') }}
                        </x-table.th>

                        <x-table.th>
                            {{ __('pattern_review.rating') }}
                        </x-table.th>

                        <x-table.th>
                            {{ __('pattern_review.comment') }}
                        </x-table.th>

                        <x-table.th>
                            {{ __('pattern_review.is_approved') }}
                        </x-table.th>

                        <x-table.th>
                            {{ __('pattern_review.user') }}
                        </x-table.th>

                        <x-table.th>
                            {{ __('pattern_review.pattern') }}
                        </x-table.th>

                        <x-table.th>
                            {{ __('pattern_review.created_at') }}
                        </x-table.th>
                    </x-table.head>
                </x-slot:header>

                <x-slot:rows>
                    @foreach ($reviews as $review)
                        <x-table.tr>
                            <x-table.td-actions>
                                @if ($review->isDeletable() === true)
                                    <x-link.button-default
                                        :href="route('admin.pattern-review.delete', ['id' => $review->id])"
                                        x-on:click.prevent="() => {deleteUrl=$el.href}"
                                    >
                                        <x-icon.svg name="delete" />
                                    </x-link.button-default>
                                @endif

                                <x-link.button-ghost :href="route('admin.page.pattern-review.edit', ['id' => $review->id])">
                                    <x-icon.svg name="edit" />
                                </x-link.button-ghost>
                            </x-table.td-actions>

                            <x-table.td>
                                {{ $review->id }}
                            </x-table.td>

                            <x-table.td>
                                {{ $review->reviewer_name }}
                            </x-table.td>

                            <x-table.td>
                                {{ $review->rating }}
                            </x-table.td>

                            <x-table.td-clamp clamp="2">
                                {{ $review->comment }}
                            </x-table.td-clamp>

                            <x-table.td-bool :value="$review->is_approved">
                                {{ $review->is_approved ? __('phrases.yes') : __('phrases.no') }}
                            </x-table.td-bool>

                            <x-table.td>
                                {{ $review->user?->name }}
                            </x-table.td>

                            <x-table.td>
                                <x-link.default
                                    :href="route('page.pattern.single', ['id' => $review->pattern->id])"
                                    target="_blank"
                                >
                                    <x-icon.svg name="external-link" />
                                </x-link.default>
                            </x-table.td>

                            <x-table.td>
                                {{ $review->created_at->translatedFormat('d F Y H:i') }}
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
                    :text="__('pattern_review.admin.confirm_delete_text')"
                >
                    @method('DELETE')
                </x-form.confirm>
            </x-modal.modal>
        </x-table.overflow-x-container>
    @endif
@endsection
