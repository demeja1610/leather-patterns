<?php

declare(strict_types=1);

namespace App\Http\Controllers\Auth;

use App\Models\User;
use Illuminate\Auth\GenericUser;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Http\RedirectResponse;
use App\Http\Requests\Auth\RegisterRequest;

class RegisterActionController extends Controller
{
    public function __invoke(RegisterRequest $request): RedirectResponse
    {
        $data =  $request->only(['name', 'email', 'password']);
        $password = Hash::make($data['password']);

        $user = $this->createUser(
            name: $data['name'],
            email: $data['email'],
            password: $password,
        );

        // avoid extra query to DB
        $genericUser = new GenericUser([
            'id' => $user->id,
            'password' => $password,
        ]);

        Auth::login($genericUser);

        return redirect(
            to: route(
                name: 'page.index',
                absolute: false,
            ),
        );
    }

    protected function createUser(string $name, string $email, string $password): User
    {
        return User::query()->create([
            'name' => $name,
            'email' => $email,
            'password' => $password,
        ]);
    }
}
