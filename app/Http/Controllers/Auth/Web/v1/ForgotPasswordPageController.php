<?php

declare(strict_types=1);

namespace App\Http\Controllers\Auth\Web\v1;

use Illuminate\Contracts\View\View;
use App\Http\Controllers\Controller;

class ForgotPasswordPageController extends Controller
{
    public function __invoke(): View
    {
        return view('pages.auth.forgot-password');
    }
}
