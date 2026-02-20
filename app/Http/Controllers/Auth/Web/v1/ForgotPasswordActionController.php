<?php

declare(strict_types=1);

namespace App\Http\Controllers\Auth\Web\v1;

use App\Models\User;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Password;
use Illuminate\Validation\ValidationException;
use App\Http\Requests\Auth\ForgotPasswordRequest;

class ForgotPasswordActionController extends Controller
{
    public function __invoke(ForgotPasswordRequest $request)
    {
        $data = $request->validated();

        $user = $this->getUser(email: $data['email']);

        if (!$user instanceof User) {
            throw ValidationException::withMessages(messages: [
                'email' => __(key: 'auth.failed'),
            ]);
        }

        $status = Password::sendResetLink(
            credentials: [
                'email' => $data['email'],
            ],
        );

        return $status === Password::RESET_LINK_SENT
            ? back()->with(key: 'status', value: __(key: $status))->withInput(input: [
                'email' => $data['email'],
            ])
            : back()->withErrors(provider: ['email' => __(key: $status)])->withInput(input: [
                'email' => $data['email'],
            ]);
    }

    protected function getUser(string $email): ?User
    {
        return User::query()
            ->where(column: 'email', operator: $email)
            ->first();
    }
}
