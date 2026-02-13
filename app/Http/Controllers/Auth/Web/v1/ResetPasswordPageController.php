<?php

declare(strict_types=1);

namespace App\Http\Controllers\Auth\Web\v1;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

class ResetPasswordPageController extends Controller
{
    public function __invoke(string $token, Request $request)
    {
        return view('pages.auth.reset-password', [
            'token' => $token,
            'email' => $request->get('email'),
        ]);
    }
}
