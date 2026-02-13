@props([
    'max' => 5,
    'stars' => 0,
])

<div {{ $attributes->merge(['class' => 'rating-stars']) }}>
    @for ($i = 1; $i <= $max; $i++)
        <x-icon.svg
            name="star"
            :class="'rating-stars__star ' . ((int) round($stars) !== 0 && $i <= (int) round($stars) ? 'rating-stars__star--active' : '')"
        />
    @endfor
</div>
