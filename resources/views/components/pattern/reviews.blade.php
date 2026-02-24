@props(['pattern'])

@if (!$pattern->reviews->isEmpty())
    <div {{ $attributes->merge(['class' => 'pattern-reviews']) }}>
        <div class="pattern-reviews__header">
            <h2 class="pattern-reviews__title">
                {{ __('pattern.reviews') }}
            </h2>

            @if ($pattern->avg_rating != 0)
                <x-rating.stars
                    class="pattern-reviews__rating-stars"
                    max="5"
                    :stars="$pattern->avg_rating"
                />
            @endif
        </div>

        <div class="pattern-reviews__list">
            @foreach ($pattern->reviews as $review)
                <div class="pattern-reviews__review">
                    <div class="pattern-reviews__review-reviewer">
                        <p class="pattern-reviews__review-reviewer-name">
                            {{ $review->reviewer_name }}
                        </p>

                        @if ($review->rating)
                            <x-rating.stars
                                class="pattern-reviews__review-reviewer-rating"
                                max="5"
                                :stars="$review->rating"
                            />
                        @endif
                    </div>

                    <div class="pattern-reviews__review-body">
                        {{ $review->comment }}
                    </div>

                    <div class="pattern-reviews__review-date">
                        {{ $review->created_at->format('d.m.Y H:i') }}
                    </div>
                </div>
            @endforeach
        </div>
    </div>
@endif
