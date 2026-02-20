<?php

declare(strict_types=1);

namespace App\Http\Controllers\Auth\Web\v1;

use Illuminate\Http\Request;
use Illuminate\Contracts\View\View;
use App\Http\Controllers\Controller;

class ResetPasswordPageController extends Controller
{
    public function __invoke(string $token, Request $request): View
    {
        return view(view: 'pages.auth.reset-password', data: [
            'token' => $token,
            'email' => $request->input(key: 'email'),
        ]);
    }
}
