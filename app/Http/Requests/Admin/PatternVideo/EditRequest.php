<?php

declare(strict_types=1);

namespace App\Http\Requests\Admin\PatternVideo;


class EditRequest extends CreateRequest
{
    public function authorize(): bool
    {
        return true;
    }
}
