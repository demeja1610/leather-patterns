@props(['pattern'])

<div
    class="pattern-list-item"
    data-gallery
>
    <a
        href="{{ route('page.pattern.single', ['id' => $pattern->id]) }}"
        class="pattern-list-item__link"
    ></a>

    @if ($pattern->avg_rating != 0)
        <x-rating.stars
            class="pattern-list-item__rating-stars"
            max="5"
            :stars="$pattern->avg_rating"
        />
    @endif

    <div class="pattern-list-item__floating-actions">
        @if ($pattern->images->count() !== 0)
            <x-button.ghost
                :title="__('pattern.zoom_image')"
                class="pattern-list-item__floating-action"
                data-gallery-trigger
            >
                <x-icon.svg name="zoom" />
            </x-button.ghost>
        @endif

        @if ($pattern->source_url !== null)
            <x-link.button-ghost
                :href="$pattern->source_url"
                :title="__('pattern.source')"
                target="_blank"
                class="pattern-list-item__floating-action"
            >
                <x-icon.svg name="globe" />
            </x-link.button-ghost>
        @endif
    </div>

    @if ($pattern->images->count() !== 0)
        <div class="pattern-list-item__image">
            @foreach ($pattern->images as $image)
                <img
                    src="{{ asset('/storage/' . $image->path) }}"
                    alt="{{ $pattern->title }}"
                    class="pattern-list-item__image-img {{ $loop->first ? 'pattern-list-item__image-img--active' : '' }}"
                    data-gallery-image
                >
            @endforeach
        </div>
    @else
        <div class="pattern-list-item__image-placeholder">
            <x-icon.svg name="image-placeholder" />
        </div>
    @endif

    <div class="pattern-list-item__content">
        <h3 class="pattern-list-item__title">
            {{ $pattern->title }}
        </h3>

        @if ($pattern->author !== null)
            <div class="pattern-list-item__authors">
                <x-badge.link
                    :href="route('page.index', ['author[]' => $pattern->author->id])"
                    class="pattern-list-item__author"
                    :text="$pattern->author->name"
                />
            </div>
        @endif

        @if ($pattern->categories !== null)
            <div class="pattern-list-item__categories">
                @foreach ($pattern->categories as $category)
                    <x-badge.link
                        :href="route('page.index', ['category[]' => $category->id])"
                        class="pattern-list-item__category"
                        :text="$category->name"
                    />
                @endforeach
            </div>
        @endif

        @if ($pattern->tags !== null)
            <div class="pattern-list-item__tags">
                @foreach ($pattern->tags as $tag)
                    <x-badge.link
                        :href="route('page.index', ['tag[]' => $tag->id])"
                        class="pattern-list-item__tag"
                        :text="$tag->name"
                    />
                @endforeach
            </div>
        @endif

        @if ($pattern->files->count() !== 0)
            <div class="pattern-list-item__downloads">
                @foreach ($pattern->files as $file)
                    <x-link.button-default
                        :title="__('pattern.download')"
                        :href="asset('/storage/' . $file->path)"
                        target="_blank"
                        :download="$file->extension !== 'pdf'"
                        class="pattern-list-item__download"
                    >
                        <x-icon.svg
                            class="pattern-list-item__download-icon pattern-list-item__download-icon--download"
                            name="download"
                        />

                        <span class="pattern-list-item__download-text">
                            {{ __('pattern.download') }}

                            @if ($pattern->files->count() > 1)
                                ({{ $loop->index + 1 }})
                            @endif
                        </span>

                        {{ $file->extension }}

                        <x-icon.svg
                            class="pattern-list-item__download-icon pattern-list-item__download-icon--{{ $file->type->value }}"
                            name="{{ $file->type->value }}-file"
                        />
                    </x-link.button-default>
                @endforeach
            </div>
        @endif

        <div class="pattern-list-item__meta">
            <div class="pattern-list-item__meta-item">
                <span class="pattern-list-item__meta-item-text">{{ __('pattern.created_at') }}:</span>
                <span class="pattern-list-item__meta-item-value">{{ $pattern->created_at->format('d.m.Y H:i') }}</span>
            </div>
        </div>
    </div>
</div>
