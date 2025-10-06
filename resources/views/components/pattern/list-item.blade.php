@props(['pattern'])

@php
    $filesCount = $pattern->files->count();
    $imagesCount = $pattern->images->count();
@endphp

<div class="pattern-list-item image-popup-container">
    <a
        href="{{ route('page.pattern.single', ['patternId' => $pattern->id]) }}"
        class="pattern-list-item__link"
    ></a>

    @if (!empty($pattern->avg_rating))
        <div class="pattern-list-item__rating">
            @for ($i = 0; $i < $pattern->avg_rating; $i++)
                <x-icon.svg name="star" />
            @endfor
        </div>
    @endif

    <div class="pattern-list-item__floating-actions">
        @if ($imagesCount !== 0)
            <button
                class="z-index-top image-popup-trigger pattern-list-item__floating-action pattern-list-item__floating-action--zoom"
                title="{{ __('pattern.zoom_image') }}"
            >
                <x-icon.svg name="zoom" />
            </button>
        @endif

        @if ($pattern->source_url !== null)
            <a
                href="{{ $pattern->source_url }}"
                target="_blank"
                class="z-index-top pattern-list-item__floating-action pattern-list-item__floating-action--source"
                title="{{ __('pattern.source') }}"
            >
                <x-icon.svg name="globe" />
            </a>
        @endif
    </div>

    @if ($imagesCount !== 0)
        <div class="pattern-list-item__image">
            @foreach ($pattern->images as $image)
                <img
                    src="{{ asset('/storage/' . $image->path) }}"
                    alt="{{ $pattern->title }}"
                    class="pattern-list-item__image-img {{ $loop->first ? 'pattern-list-item__image-img--active' : '' }} image-popup-item"
                >
            @endforeach
        </div>
    @else
        <div class="pattern-list-item__image-placeholder">
            <x-icon.svg name="image-placeholder" />
        </div>
    @endif

    <div class="pattern-list-item__content">
        <h3 class="pattern-list-item__title">{{ $pattern->title }}</h3>

        @if ($pattern->author !== null)
            <div class="pattern-list-item__authors">
                <a
                    href="{{ route('page.index', ['author[]' => $pattern->author->id]) }}"
                    class="pattern-list-item__author z-index-top"
                >
                    {{ $pattern->author->name }}
                </a>
            </div>
        @endif

        @if ($pattern->categories !== null)
            <div class="pattern-list-item__categories">
                @foreach ($pattern->categories as $category)
                    <a
                        href="{{ route('page.index', ['category[]' => $category->id]) }}"
                        class="pattern-list-item__category z-index-top"
                    >
                        {{ $category->name }}
                    </a>
                @endforeach
            </div>
        @endif

        @if ($pattern->tags !== null)
            <div class="pattern-list-item__tags">
                @foreach ($pattern->tags as $tag)
                    <a
                        href="{{ route('page.index', ['tag[]' => $tag->id]) }}"
                        class="pattern-list-item__tag z-index-top"
                    >
                        {{ $tag->name }}
                    </a>
                @endforeach
            </div>
        @endif

        @if ($filesCount !== 0)
            <div class="pattern-list-item__actions">
                @if ($filesCount !== 0)
                    @foreach ($pattern->files as $file)
                        <div class="pattern-list-item__actions-row">
                            <a
                                href="{{ asset('/storage/' . $file->path) }}"
                                class="button z-index-top pattern-list-item__actions-button pattern-list-item__actions-button--download"
                                download
                            >
                                <x-icon.svg name="download" />

                                <span class="text">
                                    {{ $file->extension }}

                                    @if ($filesCount > 1)
                                        ({{ $loop->index + 1 }})
                                    @endif
                                </span>

                                <x-icon.svg
                                    class="icon--ext icon--{{ $file->type->value }}"
                                    name="{{ $file->type->value }}-file"
                                />
                            </a>

                            @if ($file->extension === 'pdf')
                                <a
                                    href="{{ asset('/storage/' . $file->path) }}"
                                    class="button z-index-top pattern-list-item__actions-button pattern-list-item__actions-button--look"
                                    target="_blank"
                                    title="{{ __('pattern.preview') }}"
                                >
                                    <x-icon.svg name="look" />
                                </a>
                            @endif
                        </div>
                    @endforeach
                @endif
            </div>
        @endif

        <div class="pattern-list-item__meta">
            @if ($pattern->meta !== null)
                <div class="pattern-list-item__meta-item">
                    <span class="pattern-list-item__meta-item-text">{{ __('pattern.created_at') }}:</span>
                    <span class="pattern-list-item__meta-item-value">{{ $pattern->created_at->format('d.m.Y H:i') }}</span>
                </div>
            @endif
        </div>
    </div>
</div>
