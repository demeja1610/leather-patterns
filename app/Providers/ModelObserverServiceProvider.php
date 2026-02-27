<?php

declare(strict_types=1);

namespace App\Providers;

use Illuminate\Support\ServiceProvider;

class ModelObserverServiceProvider extends ServiceProvider
{
    public function register(): void {}

    public function boot(): void
    {
        \App\Models\Pattern::observe(classes: \App\Observers\PatternObserver::class);

        \App\Models\PatternReview::observe(classes: \App\Observers\PatternReviewObserver::class);

        \App\Models\PatternAuthor::observe(classes: \App\Observers\PatternAuthorObserver::class);

        \App\Models\PatternCategory::observe(classes: \App\Observers\PatternCategoryObserver::class);
    }
}
