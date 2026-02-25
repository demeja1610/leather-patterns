<?php

declare(strict_types=1);

namespace App\Http\Controllers\Admin\Pattern\Page;

use App\Enum\PatternSourceEnum;
use Illuminate\Contracts\View\View;
use App\Http\Controllers\Controller;

class CreatePageController extends Controller
{
    public function __invoke(): View
    {
        $sources = PatternSourceEnum::cases();

        return view('pages.admin.pattern.create', [
            'sources' => $sources,
        ]);
    }
}
