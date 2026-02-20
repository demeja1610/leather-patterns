<?php

declare(strict_types=1);

namespace App\Observers;

use App\Models\Pattern;
use App\Models\PatternMeta;
use App\Enum\PatternSourceEnum;

class PatternObserver
{

    public function created(Pattern $pattern): void
    {
        $this->createPatternMeta($pattern);
    }

    public function updated(Pattern $pattern): void {}

    public function deleted(Pattern $pattern): void {}

    public function restored(Pattern $pattern): void {}

    public function forceDeleted(Pattern $pattern): void {}

    protected function createPatternMeta(Pattern $pattern): void
    {
        $data = [
            'pattern_id' => $pattern->id,
        ];

        if ($pattern->source === PatternSourceEnum::MLEATHER) {
            $data['is_download_url_wrong'] = true;
        }

        PatternMeta::query()->create($data);
    }
}
