@extends('layouts.app')

@section('content')
    <div class="page page--single-pattern">
        <div class="single-pattern">
            <div class="single-pattern__header">
                <h1 class="single-pattern__title">{{ $pattern->title }}</h1>

                @if ($pattern->author)
                    <div class="single-pattern__authors">
                        <h3 class="single-pattern__authors-title">
                            {{ __('pattern.authors') }}:
                        </h3>

                        <div class="single-pattern__authors-list">
                            <a
                                href="{{ route('page.index', ['author[]' => $pattern->author->id]) }}"
                                class="single-pattern__author"
                            >
                                {{ $pattern->author->name }}
                            </a>
                        </div>
                    </div>
                @endif

                @if ($pattern->categories && !$pattern->categories->isEmpty())
                    <div class="single-pattern__categories">
                        <h3 class="single-pattern__categories-title">
                            {{ __('pattern.categories') }}:
                        </h3>

                        <div class="single-pattern__categories-list">
                            @foreach ($pattern->categories as $category)
                                <a
                                    href="{{ route('page.index', ['category[]' => $category->id]) }}"
                                    class="single-pattern__category"
                                >
                                    {{ $category->name }}
                                </a>
                            @endforeach
                        </div>
                    </div>
                @endif

                @if ($pattern->tags && !$pattern->tags->isEmpty())
                    <div class="single-pattern__tags">
                        <h3 class="single-pattern__tags-title">
                            {{ __('pattern.tags') }}:
                        </h3>

                        <div class="single-pattern__tags-list">
                            @foreach ($pattern->tags as $tag)
                                <a
                                    href="{{ route('page.index', ['tag[]' => $tag->id]) }}"
                                    class="single-pattern__tag"
                                >{{ $tag->name }}</a>
                            @endforeach
                        </div>
                    </div>
                @endif
            </div>

            <div class="single-pattern__content">
                @if ($pattern->images && !$pattern->images->isEmpty())
                    <div class="single-pattern__images">
                        @foreach ($pattern->images as $image)
                            <a
                                href="{{ asset('/storage/' . $image->path) }}"
                                class="single-pattern__image"
                                target="_blank"
                                data-fslightbox
                            >
                                <img
                                    src="{{ asset('/storage/' . $image->path) }}"
                                    alt="{{ $pattern->title }}"
                                    class="single-pattern__image-img"
                                >
                            </a>
                        @endforeach
                    </div>
                @endif

                @if ($pattern->videos && !$pattern->videos->isEmpty())
                    <div class="single-pattern__videos">
                        <h3 class="single-pattern__videos-title">
                            {{ __('pattern.video') }}:
                        </h3>

                        <div class="single-pattern__videos-list">
                            @foreach ($pattern->videos as $video)
                                <div class="single-pattern__video">
                                    <iframe
                                        src="{{ $video->embed_url }}"
                                        class="single-pattern__video-iframe"
                                        allowfullscreen
                                    ></iframe>
                                </div>
                            @endforeach
                        </div>
                    </div>
                @endif

                <div class="single-pattern__actions">
                    @if ($pattern->source_url !== null)
                        <a
                            href="{{ $pattern->source_url }}"
                            target="_blank"
                            class="button button--ghost"
                            title="{{ __('pattern.go_to_source') }}"
                        >
                            {{ __('pattern.go_to_source') }}
                        </a>
                    @endif

                    @php
                        $filesCount = $pattern->files->count();
                    @endphp

                    @if ($filesCount !== 0)
                        @foreach ($pattern->files as $file)
                            <a
                                href="{{ asset('/storage/' . $file->path) }}"
                                class="button single-pattern__download-button"
                                download
                                title="{{ __('pattern.download') }}"
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
                        @endforeach
                    @endif
                </div>
            </div>

            @if ($pattern->reviews->count() !== 0)
                <div class="single-pattern__reviews">
                    <div class="single-pattern__reviews-header">
                        <h2 class="single-pattern__reviews-title">
                            {{ __('pattern.reviews') }}
                        </h2>

                        @if (!empty($pattern->avg_rating))
                            <div class="single-pattern__average-rating">
                                @for ($i = 0; $i < $pattern->avg_rating; $i++)
                                    <x-icon.svg name="star" />
                                @endfor
                            </div>
                        @endif
                    </div>

                    <div class="single-pattern__reviews-list">
                        @foreach ($pattern->reviews as $review)
                            <div class="single-pattern__review">
                                <div class="single-pattern__review-reviewer">
                                    <p class="single-pattern__review-reviewer-name">
                                        {{ $review->reviewer_name }}
                                    </p>

                                    @if ($review->rating)
                                        <div class="single-pattern__review-reviewer-rating">
                                            @for ($i = 0; $i < $review->rating; $i++)
                                                <x-icon.svg name="star" />
                                            @endfor
                                        </div>
                                    @endif
                                </div>

                                <div class="single-pattern__review-body">
                                    {{ $review->comment }}
                                </div>

                                <div class="single-pattern__review-date">
                                    {{ $review->reviewed_at->format('d.m.Y H:i') }}
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>
            @endif
        </div>
    </div>
@endsection
