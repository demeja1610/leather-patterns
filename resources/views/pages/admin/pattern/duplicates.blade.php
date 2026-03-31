@extends('layouts.admin.list', [
    'paginator' => $duplicates,
    'title' => __('pattern.duplicates'),
    'showFilters' => $activeFilters !== [],
    'filterUrl' => route('admin.page.patterns.duplicates'),
    'resetUrl' => route('admin.page.patterns.duplicates'),
    'classes' => 'admin-page-patterns-duplicates',
])

@section('page-filters')
    <x-input-text.input-text>
        <x-input-text.label for="id">
            {{ __('filter.duplicates_count') }}
        </x-input-text.label>

        <x-input-text.input
            id="duplicates_count"
            name="duplicates_count"
            type="number"
            :value="$activeFilters['duplicates_count'] ?? null"
            :title="__('filter.duplicates_count')"
        />
    </x-input-text.input-text>
@endsection

@section('page')
    @if ($duplicates->isEmpty())
        <x-table.empty>
            {{ __('phrases.nothing_found') }}
        </x-table.empty>
    @else
        <x-table.overflow-x-container>
            <x-table.table>
                <x-slot:header>
                    <x-table.head>
                        <x-table.th>
                            {{ __('pattern_file.hash') }}
                        </x-table.th>

                        <x-table.th>
                            {{ __('pattern.duplicates_count') }}
                        </x-table.th>

                        <x-table.th>
                            {{ __('pattern.public_pattern_links') }}
                        </x-table.th>

                        <x-table.th>
                            {{ __('pattern.admin_pattern_links') }}
                        </x-table.th>
                    </x-table.head>
                </x-slot:header>

                <x-slot:rows>
                    @foreach ($duplicates as $duplicate)
                        <x-table.tr>
                            <x-table.td>
                                {{ $duplicate->getHash() }}
                            </x-table.td>

                            <x-table.td>
                                {{ $duplicate->getDuplicatesCount() }}
                            </x-table.td>

                            <x-table.td>
                                <div class="admin-page-patterns-duplicates__public-links">
                                    @foreach ($duplicate->getPatternIds() as $patternId)
                                        <x-link.button-ghost
                                            :href="route('page.pattern.single', ['id' => $patternId])"
                                            target="_blank"
                                        >
                                            {{ $patternId }}

                                            <x-icon.svg name="external-link" />
                                        </x-link.button-ghost>
                                    @endforeach
                                </div>
                            </x-table.td>

                            <x-table.td>
                                <div class="admin-page-patterns-duplicates__admin-links">
                                    @foreach ($duplicate->getPatternIds() as $patternId)
                                        <x-link.button-default
                                            :href="route('admin.page.patterns.list', ['id' => $patternId])"
                                            target="_blank"
                                        >
                                            {{ $patternId }}

                                            <x-icon.svg name="external-link" />
                                        </x-link.button-default>
                                    @endforeach
                                </div>
                            </x-table.td>




                            {{-- <x-table.td>
                                @if ($pattern->source->value === 'local')
                                    {{ __("pattern_source.{$pattern->source->value}") }}
                                @else
                                    <x-link.default
                                        :href="$pattern->source_url"
                                        target="_blank"
                                        class="admin-page-patterns-list__source-link"
                                    >
                                        {{ __("pattern_source.{$pattern->source->value}") }}

                                        <x-icon.svg name="external-link" />
                                    </x-link.default>
                                @endif
                            </x-table.td>

                            <x-table.td-clamp
                                clamp="2"
                                :title="$pattern->title"
                            >
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
                            </x-table.td> --}}
                        </x-table.tr>
                    @endforeach
                </x-slot:rows>
            </x-table.table>
        </x-table.overflow-x-container>
    @endif
@endsection
