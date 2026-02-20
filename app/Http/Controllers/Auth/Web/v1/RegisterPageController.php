<?php

declare(strict_types=1);

namespace App\Http\Controllers\Auth\Web\v1;

use Illuminate\View\View;
use App\Http\Controllers\Controller;

class RegisterPageController extends Controller
{
    public function __invoke(): View
    {
        return view(view: 'pages.auth.register');
    }
}
