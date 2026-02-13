<?php

declare(strict_types=1);

namespace App\Http\Controllers\Auth\Web\v1;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Http\RedirectResponse;

class LogoutActionController extends Controller
{
    public function __invoke(Request $request): RedirectResponse
    {
        Auth::guard('web')->logout();

        $request->session()->invalidate();

        $request->session()->regenerateToken();

        return redirect(
            to: route(
                name: 'page.index',
                absolute: false,
            ),
        );
    }
}
