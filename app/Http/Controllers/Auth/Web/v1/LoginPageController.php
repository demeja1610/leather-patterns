<?php

declare(strict_types=1);

namespace App\Http\Controllers\Auth\Web\v1;

use Illuminate\View\View;
use App\Http\Controllers\Controller;

class LoginPageController extends Controller
{
    public function __invoke(): View
    {
        return view('pages.auth.login');
    }
}
