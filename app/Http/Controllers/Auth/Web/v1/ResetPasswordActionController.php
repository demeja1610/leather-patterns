<?php

declare(strict_types=1);

namespace App\Http\Controllers\Auth\Web\v1;

use App\Models\User;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Password;
use App\Http\Requests\Auth\ResetPasswordRequest;

class ResetPasswordActionController extends Controller
{
    public function __invoke(ResetPasswordRequest $request)
    {
        $data = $request->validated();

        $status = Password::reset(
            $data,
            function (User $user) use (&$data): void {
                $user->forceFill(attributes: [
                    'password' => Hash::make($data['password']),
                ])->save();
            },
        );

        return $status === Password::PASSWORD_RESET
            ? to_route(route: 'page.auth.login')->with(key: 'status', value: __(key: $status))
            : back()->withErrors(provider: ['password' => __(key: $status)]);
    }
}
