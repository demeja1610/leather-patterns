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

    <x-context-menu.context-menu class="pattern-list-item__menu">
        @if ($pattern->images->count() !== 0)
            <x-context-menu.context-menu-item-button
                data-gallery-trigger
                :title="__('pattern.show_gallery')"
            >
                <x-icon.svg name="zoom" />

                {{ __('pattern.show_gallery') }}
            </x-context-menu.context-menu-item-button>
        @endif

        @if ($pattern->source_url !== null)
            <x-context-menu.context-menu-item-link
                :href="$pattern->source_url"
                :title="__('pattern.source')"
                target="_blank"
            >
                <x-icon.svg name="globe" />

                {{ __('pattern.source') }}
            </x-context-menu.context-menu-item-link>
        @endif

        @if ($pattern->files->count() !== 0)
            @if ($pattern->files->count() === 1)
                <x-context-menu.context-menu-item-link
                    :href="asset('/storage/' . $pattern->files->first()->path)"
                    :title="__('pattern.download')"
                    target="_blank"
                    :download="basename($pattern->files->first()->path)"
                >
                    <x-icon.svg name="download" />

                    {{ __('pattern.download') }}
                </x-context-menu.context-menu-item-link>
            @else
                @foreach ($pattern->files as $file)
                    <x-context-menu.context-menu-item-link
                        :href="asset('/storage/' . $file->first()->path)"
                        :title="__('pattern.download')"
                        target="_blank"
                        :download="basename($file->first()->path)"
                    >
                        <x-icon.svg name="download" />

                        {{ __('pattern.download') . ' ' . $loop->index + 1 }}
                    </x-context-menu.context-menu-item-link>
                @endforeach
            @endif
        @endif
    </x-context-menu.context-menu>

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

        @if ($pattern->categories && $pattern->categories->isEmpty() === false)
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

        @if ($pattern->tags && $pattern->tags->isEmpty() === false)
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

        <div class="pattern-list-item__meta">
            <div class="pattern-list-item__meta-item">
                <span class="pattern-list-item__meta-item-text">{{ __('pattern.created_at') }}:</span>
                <span class="pattern-list-item__meta-item-value">{{ $pattern->created_at->format('d.m.Y H:i') }}</span>
            </div>
        </div>
    </div>
</div>
