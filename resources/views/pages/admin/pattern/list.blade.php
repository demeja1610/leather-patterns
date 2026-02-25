@extends('layouts.admin.list', [
    'paginator' => $patterns,
    'title' => __('pattern.patterns'),
    'showFilters' => $activeFilters !== [],
    'filterUrl' => route('admin.page.patterns.list'),
    'resetUrl' => route('admin.page.patterns.list'),
    'classes' => 'admin-page-patterns-list',
])

{{-- @section('header-content')
    <x-link.button-default :href="route('admin.pattern.create')">
        <x-icon.svg name="create" />

        {{ __('actions.add_new') }}
    </x-link.button-default>
@endsection --}}

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
        <x-input-text.label for="title">
            {{ __('filter.title') }}
        </x-input-text.label>

        <x-input-text.input
            id="title"
            name="title"
            type="text"
            :value="$activeFilters['title'] ?? null"
            :title="__('filter.title')"
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
        <x-select.label for="has_image">
            {{ __('pattern.has_image') }}
        </x-select.label>

        <x-select.select
            name="has_image"
            id="has_image"
            :title="__('pattern.has_image')"
        >
            <x-select.option
                value=""
                :selected="!isset($activeFilters['has_image'])"
            >
                {{ __('filter.not_selected') }}
            </x-select.option>

            <x-select.option
                value="1"
                :selected="isset($activeFilters['has_image']) && $activeFilters['has_image'] === true"
            >
                {{ __('phrases.yes') }}
            </x-select.option>

            <x-select.option
                value="0"
                :selected="isset($activeFilters['has_image']) && $activeFilters['has_image'] === false"
            >
                {{ __('phrases.no') }}
            </x-select.option>

        </x-select.select>
    </x-select.wrapper>

    <x-select.wrapper>
        <x-select.label for="is_published">
            {{ __('pattern.is_published') }}
        </x-select.label>

        <x-select.select
            name="is_published"
            id="is_published"
            :title="__('pattern.is_published')"
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
        <x-select.label for="has_categories">
            {{ __('pattern.has_categories') }}
        </x-select.label>

        <x-select.select
            name="has_categories"
            id="has_categories"
            :title="__('pattern.has_categories')"
        >
            <x-select.option
                value=""
                :selected="!isset($activeFilters['has_categories'])"
            >
                {{ __('filter.not_selected') }}
            </x-select.option>

            <x-select.option
                value="1"
                :selected="isset($activeFilters['has_categories']) && $activeFilters['has_categories'] === true"
            >
                {{ __('phrases.yes') }}
            </x-select.option>

            <x-select.option
                value="0"
                :selected="isset($activeFilters['has_categories']) && $activeFilters['has_categories'] === false"
            >
                {{ __('phrases.no') }}
            </x-select.option>

        </x-select.select>
    </x-select.wrapper>

    <x-fetch-select.single
        :url="route('api.admin.v1.pattern-category.search')"
        id="category_id"
        name="category_id"
        :label="__('pattern.category')"
        :placeholder="__('phrases.search')"
        keyName="id"
        valueName="name"
        :selectedKey="isset($activeFilters['category_id']) ? $activeFilters['category_id'] : null"
        :selectedValue="isset($extraData['category_name']) ? $extraData['category_name'] : null"
    />

    <x-select.wrapper>
        <x-select.label for="has_tags">
            {{ __('pattern.has_tags') }}
        </x-select.label>

        <x-select.select
            name="has_tags"
            id="has_tags"
            :title="__('pattern.has_tags')"
        >
            <x-select.option
                value=""
                :selected="!isset($activeFilters['has_tags'])"
            >
                {{ __('filter.not_selected') }}
            </x-select.option>

            <x-select.option
                value="1"
                :selected="isset($activeFilters['has_tags']) && $activeFilters['has_tags'] === true"
            >
                {{ __('phrases.yes') }}
            </x-select.option>

            <x-select.option
                value="0"
                :selected="isset($activeFilters['has_tags']) && $activeFilters['has_tags'] === false"
            >
                {{ __('phrases.no') }}
            </x-select.option>

        </x-select.select>
    </x-select.wrapper>

    <x-fetch-select.single
        :url="route('api.admin.v1.pattern-tag.search')"
        id="tag_id"
        name="tag_id"
        :label="__('pattern.tag')"
        :placeholder="__('phrases.search')"
        keyName="id"
        valueName="name"
        :selectedKey="isset($activeFilters['tag_id']) ? $activeFilters['tag_id'] : null"
        :selectedValue="isset($extraData['tag_name']) ? $extraData['tag_name'] : null"
    />

    <x-select.wrapper>
        <x-select.label for="has_author">
            {{ __('pattern.has_author') }}
        </x-select.label>

        <x-select.select
            name="has_author"
            id="has_author"
            :title="__('pattern.has_author')"
        >
            <x-select.option
                value=""
                :selected="!isset($activeFilters['has_author'])"
            >
                {{ __('filter.not_selected') }}
            </x-select.option>

            <x-select.option
                value="1"
                :selected="isset($activeFilters['has_author']) && $activeFilters['has_author'] === true"
            >
                {{ __('phrases.yes') }}
            </x-select.option>

            <x-select.option
                value="0"
                :selected="isset($activeFilters['has_author']) && $activeFilters['has_author'] === false"
            >
                {{ __('phrases.no') }}
            </x-select.option>
        </x-select.select>
    </x-select.wrapper>

    <x-fetch-select.single
        :url="route('api.admin.v1.pattern-author.search')"
        id="author_id"
        name="author_id"
        :label="__('pattern.author')"
        :placeholder="__('phrases.search')"
        keyName="id"
        valueName="name"
        :selectedKey="isset($activeFilters['author_id']) ? $activeFilters['author_id'] : null"
        :selectedValue="isset($extraData['author_name']) ? $extraData['author_name'] : null"
    />

    <x-select.wrapper>
        <x-select.label for="has_files">
            {{ __('pattern.has_files') }}
        </x-select.label>

        <x-select.select
            name="has_files"
            id="has_files"
            :title="__('pattern.has_files')"
        >
            <x-select.option
                value=""
                :selected="!isset($activeFilters['has_files'])"
            >
                {{ __('filter.not_selected') }}
            </x-select.option>

            <x-select.option
                value="1"
                :selected="isset($activeFilters['has_files']) && $activeFilters['has_files'] === true"
            >
                {{ __('phrases.yes') }}
            </x-select.option>

            <x-select.option
                value="0"
                :selected="isset($activeFilters['has_files']) && $activeFilters['has_files'] === false"
            >
                {{ __('phrases.no') }}
            </x-select.option>
        </x-select.select>
    </x-select.wrapper>

    <x-select.wrapper>
        <x-select.label for="has_videos">
            {{ __('pattern.has_videos') }}
        </x-select.label>

        <x-select.select
            name="has_videos"
            id="has_videos"
            :title="__('pattern.has_videos')"
        >
            <x-select.option
                value=""
                :selected="!isset($activeFilters['has_videos'])"
            >
                {{ __('filter.not_selected') }}
            </x-select.option>

            <x-select.option
                value="1"
                :selected="isset($activeFilters['has_videos']) && $activeFilters['has_videos'] === true"
            >
                {{ __('phrases.yes') }}
            </x-select.option>

            <x-select.option
                value="0"
                :selected="isset($activeFilters['has_videos']) && $activeFilters['has_videos'] === false"
            >
                {{ __('phrases.no') }}
            </x-select.option>
        </x-select.select>
    </x-select.wrapper>

    <x-select.wrapper>
        <x-select.label for="has_reviews">
            {{ __('pattern.has_reviews') }}
        </x-select.label>

        <x-select.select
            name="has_reviews"
            id="has_reviews"
            :title="__('pattern.has_reviews')"
        >
            <x-select.option
                value=""
                :selected="!isset($activeFilters['has_reviews'])"
            >
                {{ __('filter.not_selected') }}
            </x-select.option>

            <x-select.option
                value="1"
                :selected="isset($activeFilters['has_reviews']) && $activeFilters['has_reviews'] === true"
            >
                {{ __('phrases.yes') }}
            </x-select.option>

            <x-select.option
                value="0"
                :selected="isset($activeFilters['has_reviews']) && $activeFilters['has_reviews'] === false"
            >
                {{ __('phrases.no') }}
            </x-select.option>
        </x-select.select>
    </x-select.wrapper>

    <x-select.wrapper>
        <x-select.label for="pattern_downloaded">
            {{ __('pattern.pattern_downloaded') }}
        </x-select.label>

        <x-select.select
            name="pattern_downloaded"
            id="pattern_downloaded"
            :title="__('pattern.pattern_downloaded')"
        >
            <x-select.option
                value=""
                :selected="!isset($activeFilters['pattern_downloaded'])"
            >
                {{ __('filter.not_selected') }}
            </x-select.option>

            <x-select.option
                value="1"
                :selected="isset($activeFilters['pattern_downloaded']) && $activeFilters['pattern_downloaded'] === true"
            >
                {{ __('phrases.yes') }}
            </x-select.option>

            <x-select.option
                value="0"
                :selected="isset($activeFilters['pattern_downloaded']) && $activeFilters['pattern_downloaded'] === false"
            >
                {{ __('phrases.no') }}
            </x-select.option>
        </x-select.select>
    </x-select.wrapper>

    <x-select.wrapper>
        <x-select.label for="images_downloaded">
            {{ __('pattern.images_downloaded') }}
        </x-select.label>

        <x-select.select
            name="images_downloaded"
            id="images_downloaded"
            :title="__('pattern.images_downloaded')"
        >
            <x-select.option
                value=""
                :selected="!isset($activeFilters['images_downloaded'])"
            >
                {{ __('filter.not_selected') }}
            </x-select.option>

            <x-select.option
                value="1"
                :selected="isset($activeFilters['images_downloaded']) && $activeFilters['images_downloaded'] === true"
            >
                {{ __('phrases.yes') }}
            </x-select.option>

            <x-select.option
                value="0"
                :selected="isset($activeFilters['images_downloaded']) && $activeFilters['images_downloaded'] === false"
            >
                {{ __('phrases.no') }}
            </x-select.option>
        </x-select.select>
    </x-select.wrapper>

    <x-select.wrapper>
        <x-select.label for="is_download_url_wrong">
            {{ __('pattern.is_download_url_wrong') }}
        </x-select.label>

        <x-select.select
            name="is_download_url_wrong"
            id="is_download_url_wrong"
            :title="__('pattern.is_download_url_wrong')"
        >
            <x-select.option
                value=""
                :selected="!isset($activeFilters['is_download_url_wrong'])"
            >
                {{ __('filter.not_selected') }}
            </x-select.option>

            <x-select.option
                value="1"
                :selected="isset($activeFilters['is_download_url_wrong']) && $activeFilters['is_download_url_wrong'] === true"
            >
                {{ __('phrases.yes') }}
            </x-select.option>

            <x-select.option
                value="0"
                :selected="isset($activeFilters['is_download_url_wrong']) && $activeFilters['is_download_url_wrong'] === false"
            >
                {{ __('phrases.no') }}
            </x-select.option>
        </x-select.select>
    </x-select.wrapper>

    <x-select.wrapper>
        <x-select.label for="is_video_checked">
            {{ __('pattern.is_video_checked') }}
        </x-select.label>

        <x-select.select
            name="is_video_checked"
            id="is_video_checked"
            :title="__('pattern.is_video_checked')"
        >
            <x-select.option
                value=""
                :selected="!isset($activeFilters['is_video_checked'])"
            >
                {{ __('filter.not_selected') }}
            </x-select.option>

            <x-select.option
                value="1"
                :selected="isset($activeFilters['is_video_checked']) && $activeFilters['is_video_checked'] === true"
            >
                {{ __('phrases.yes') }}
            </x-select.option>

            <x-select.option
                value="0"
                :selected="isset($activeFilters['is_video_checked']) && $activeFilters['is_video_checked'] === false"
            >
                {{ __('phrases.no') }}
            </x-select.option>
        </x-select.select>
    </x-select.wrapper>
@endsection

@section('page')
    @if ($patterns->isEmpty())
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
                            {{ __('pattern.id') }}
                        </x-table.th>

                        <x-table.th>
                            {{ __('pattern.image_short') }}
                        </x-table.th>

                        <x-table.th>
                            {{ __('pattern.source') }}
                        </x-table.th>

                        <x-table.th>
                            {{ __('pattern.title') }}
                        </x-table.th>

                        <x-table.th>
                            {{ __('pattern.is_published') }}
                        </x-table.th>

                        <x-table.th>
                            {{ __('pattern.author') }}
                        </x-table.th>

                        <x-table.th>
                            {{ __('pattern.categories') }}
                        </x-table.th>

                        <x-table.th>
                            {{ __('pattern.tags') }}
                        </x-table.th>

                        <x-table.th>
                            {{ __('pattern.files_count') }}
                        </x-table.th>

                        <x-table.th>
                            {{ __('pattern.videos_count') }}
                        </x-table.th>

                        <x-table.th>
                            {{ __('pattern.reviews_count') }}
                        </x-table.th>

                        <x-table.th>
                            {{ __('pattern.meta.meta') }}
                        </x-table.th>

                        <x-table.th>
                            {{ __('pattern.created_at') }}
                        </x-table.th>
                    </x-table.head>
                </x-slot:header>

                <x-slot:rows>
                    @foreach ($patterns as $pattern)
                        <x-table.tr>
                            <x-table.td-actions>
                                @if ($pattern->isDeletable() === true)
                                    <x-link.button-default
                                        :href="route('admin.pattern.delete', ['id' => $pattern->id])"
                                        x-on:click.prevent="() => {deleteUrl=$el.href}"
                                    >
                                        <x-icon.svg name="delete" />
                                    </x-link.button-default>
                                @endif

                                {{-- <x-link.button-ghost :href="route('admin.page.patterns.edit', ['id' => $pattern->id])">
                                    <x-icon.svg name="edit" />
                                </x-link.button-ghost> --}}
                            </x-table.td-actions>

                            <x-table.td>
                                {{ $pattern->id }}
                            </x-table.td>

                            <x-table.td-image
                                :image="$pattern->images->isEmpty() ? null : asset('/storage/' . $pattern->images->first()->path)"
                                :alt="$pattern->title"
                            />

                            <x-table.td>
                                <x-link.default
                                    :href="$pattern->source_url"
                                    target="_blank"
                                    class="admin-page-patterns-list__source-link"
                                >
                                    {{ __("pattern_source.{$pattern->source->value}") }}

                                    <x-icon.svg name="external-link" />
                                </x-link.default>
                            </x-table.td>

                            <x-table.td-clamp clamp="2">
                                {{ $pattern->title }}
                            </x-table.td-clamp>

                            <x-table.td-bool :value="$pattern->is_published">
                                {{ $pattern->is_published ? __('phrases.yes') : __('phrases.no') }}
                            </x-table.td-bool>

                            <x-table.td>
                                @if ($pattern->author !== null)
                                    <div class="admin-page-patterns-list__pattern-authors">
                                        <x-badge.link
                                            :href="route('admin.page.patterns.list', ['author_id' => $pattern->author->id])"
                                            :text="$pattern->author->name"
                                            class="admin-page-patterns-list__pattern-author"
                                        />
                                    </div>
                                @endif
                            </x-table.td>

                            <x-table.td>
                                <div class="admin-page-patterns-list__pattern-categories">
                                    @foreach ($pattern->categories as $category)
                                        <x-badge.link
                                            :href="route('admin.page.patterns.list', ['category_id' => $category->id])"
                                            :text="$category->name"
                                            class="admin-page-patterns-list__pattern-category"
                                        />
                                    @endforeach
                                </div>
                            </x-table.td>

                            <x-table.td>
                                <div class="admin-page-patterns-list__pattern-tags">
                                    @foreach ($pattern->tags as $tag)
                                        <x-badge.link
                                            :href="route('admin.page.patterns.list', ['tag_id' => $tag->id])"
                                            :text="$tag->name"
                                            class="admin-page-patterns-list__pattern-tag"
                                        />
                                    @endforeach
                                </div>
                            </x-table.td>

                            <x-table.td>
                                {{ $pattern->files_count }}
                            </x-table.td>

                            <x-table.td>
                                {{ $pattern->videos_count }}
                            </x-table.td>

                            <x-table.td>
                                {{ $pattern->reviews_count }}
                            </x-table.td>

                            <x-table.td-dropdown :headerText="__('phrases.list')">
                                <ul class="admin-page-patterns-list__meta-list">
                                    <li class="admin-page-patterns-list__meta">
                                        <span class="admin-page-patterns-list__meta-name">
                                            {{ __('pattern.meta.pattern_downloaded') }}:
                                        </span>

                                        <x-bool.bool
                                            :value="$pattern->meta->pattern_downloaded"
                                            class="admin-page-patterns-list__meta-value"
                                        >
                                            {{ $pattern->meta->pattern_downloaded ? __('phrases.yes') : __('phrases.no') }}
                                        </x-bool.bool>
                                    </li>

                                    <li class="admin-page-patterns-list__meta">
                                        <span class="admin-page-patterns-list__meta-name">
                                            {{ __('pattern.meta.images_downloaded') }}:
                                        </span>

                                        <x-bool.bool
                                            :value="$pattern->meta->images_downloaded"
                                            class="admin-page-patterns-list__meta-value"
                                        >
                                            {{ $pattern->meta->images_downloaded ? __('phrases.yes') : __('phrases.no') }}
                                        </x-bool.bool>
                                    </li>

                                    <li class="admin-page-patterns-list__meta">
                                        <span class="admin-page-patterns-list__meta-name">
                                            {{ __('pattern.meta.is_download_url_wrong') }}:
                                        </span>

                                        <x-bool.bool-reverse
                                            :value="$pattern->meta->is_download_url_wrong"
                                            class="admin-page-patterns-list__meta-value"
                                        >
                                            {{ $pattern->meta->is_download_url_wrong ? __('phrases.yes') : __('phrases.no') }}
                                        </x-bool.bool-reverse>
                                    </li>

                                    <li class="admin-page-patterns-list__meta">
                                        <span class="admin-page-patterns-list__meta-name">
                                            {{ __('pattern.meta.is_video_checked') }}:
                                        </span>

                                        <x-bool.bool
                                            :value="$pattern->meta->is_video_checked"
                                            class="admin-page-patterns-list__meta-value"
                                        >
                                            {{ $pattern->meta->is_video_checked ? __('phrases.yes') : __('phrases.no') }}
                                        </x-bool.bool>
                                    </li>

                                    <li class="admin-page-patterns-list__meta">
                                        <span class="admin-page-patterns-list__meta-name">
                                            {{ __('pattern.meta.reviews_updated_at') }}:
                                        </span>

                                        <span class="admin-page-patterns-list__meta-value">
                                            @if ($pattern->meta->reviews_updated_at)
                                                {{ $pattern->meta->reviews_updated_at?->translatedFormat('d F Y H:i') }}
                                            @else
                                                {{ __('phrases.empty') }}
                                            @endif
                                        </span>
                                    </li>
                                </ul>
                            </x-table.td-dropdown>

                            <x-table.td>
                                {{ $pattern->created_at->translatedFormat('d F Y H:i') }}
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
                    :text="__('pattern.admin.confirm_delete_text')"
                >
                    @method('DELETE')
                </x-form.confirm>
            </x-modal.modal>
        </x-table.overflow-x-container>
    @endif
@endsection
