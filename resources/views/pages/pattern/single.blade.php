@extends('layouts.app')

@section('content')
    <div class="page page-single-pattern">
        <div class="page-single-pattern__header">
            <h1 class="page-single-pattern__title">
                {{ $pattern->title }}

                @if ($pattern->source_url !== null)
                    <x-badge.link
                        :href="$pattern->source_url"
                        class="page-single-pattern__source"
                        :title="__('pattern.go_to_source')"
                        target="_blank"
                    >
                        <x-icon.svg
                            class="page-single-pattern__source-icon"
                            name="globe"
                        />

                        {{ __('pattern.go_to_source') }}
                    </x-badge.link>
                @endif
            </h1>
        </div>

        @if ($pattern->author)
            <div class="page-single-pattern__authors">
                <h3 class="page-single-pattern__authors-title">
                    {{ __('pattern.authors') }}:
                </h3>

                <x-badge.link
                    :href="route('page.index', ['author[]' => $pattern->author->id])"
                    class="page-single-pattern__authors-item"
                    :text="$pattern->author->name"
                />
            </div>
        @endif

        @if ($pattern->categories && !$pattern->categories->isEmpty())
            <div class="page-single-pattern__categories">
                <h3 class="page-single-pattern__categories-title">
                    {{ __('pattern.categories') }}:
                </h3>

                @foreach ($pattern->categories as $category)
                    <x-badge.link
                        :href="route('page.index', ['category[]' => $category->id])"
                        class="page-single-pattern__categories-item"
                        :text="$category->name"
                    />
                @endforeach
            </div>
        @endif

        @if ($pattern->tags && !$pattern->tags->isEmpty())
            <div class="page-single-pattern__tags">
                <h3 class="page-single-pattern__tags-title">
                    {{ __('pattern.tags') }}:
                </h3>

                @foreach ($pattern->tags as $tag)
                    <x-badge.link
                        :href="route('page.index', ['tag[]' => $tag->id])"
                        class="page-single-pattern__tags-item"
                        :text="$tag->name"
                    />
                @endforeach
            </div>
        @endif

        <div class="page-single-pattern__content">
            @if ($pattern->images && !$pattern->images->isEmpty())
                <div class="page-single-pattern__images">
                    @foreach ($pattern->images as $image)
                        <a
                            href="{{ asset('/storage/' . $image->path) }}"
                            class="page-single-pattern__image"
                            target="_blank"
                            data-fslightbox
                        >
                            <img
                                src="{{ asset('/storage/' . $image->path) }}"
                                alt="{{ $pattern->title }}"
                                class="page-single-pattern__image-img"
                            >
                        </a>
                    @endforeach
                </div>
            @endif

            @if ($pattern->videos && !$pattern->videos->isEmpty())
                <div class="page-single-pattern__videos">
                    <h3 class="page-single-pattern__videos-title">
                        {{ __('pattern.video') }}:
                    </h3>

                    <div class="page-single-pattern__videos-list">
                        @foreach ($pattern->videos as $video)
                            <div class="page-single-pattern__video">
                                <iframe
                                    src="{{ $video->embed_url }}"
                                    class="page-single-pattern__video-iframe"
                                    allowfullscreen
                                ></iframe>
                            </div>
                        @endforeach
                    </div>
                </div>
            @endif

            <div class="page-single-pattern__actions">
                @foreach ($pattern->files as $file)
                    <x-link.button-default
                        :title="__('pattern.download')"
                        :href="asset('/storage/' . $file->path)"
                        target="_blank"
                        :download="$file->extension !== 'pdf'"
                        class="page-single-pattern__download"
                    >
                        <x-icon.svg
                            class="page-single-pattern__download-icon page-single-pattern__download-icon--download"
                            name="download"
                        />

                        <span class="page-single-pattern__download-text">
                            {{ __('pattern.download') }}

                            @if ($pattern->files->count() > 1)
                                ({{ $loop->index + 1 }})
                            @endif
                        </span>

                        {{ $file->extension }}

                        <x-icon.svg
                            class="page-single-pattern__download-icon page-single-pattern__download-icon--{{ $file->type->value }}"
                            name="{{ $file->type->value }}-file"
                        />
                    </x-link.button-default>
                @endforeach
            </div>
        </div>

        <x-pattern.reviews :pattern="$pattern" />
    </div>
@endsection
