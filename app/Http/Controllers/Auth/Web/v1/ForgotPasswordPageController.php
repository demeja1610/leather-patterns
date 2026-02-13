<?php

declare(strict_types=1);

namespace App\Http\Controllers\Auth\Web\v1;

use App\Http\Controllers\Controller;

class ForgotPasswordPageController extends Controller
{
    public function __invoke()
    {
        return view('pages.auth.forgot-password');
    }
}
