<?php

declare(strict_types=1);

use Illuminate\Support\Facades\Route;

Route::middleware('web')
    ->group(function (): void {
        Route::middleware('guest')->group(function (): void {
            Route::get('/login', \App\Http\Controllers\Auth\LoginPageController::class)
                ->name('page.auth.login');

            Route::post('login', \App\Http\Controllers\Auth\LoginActionController::class)
                ->name('auth.login');

            Route::get('register', \App\Http\Controllers\Auth\RegisterPageController::class)
                ->name('page.auth.register');

            Route::post('register', \App\Http\Controllers\Auth\RegisterActionController::class)
                ->name('auth.register');

            Route::get('forgot-password', \App\Http\Controllers\Auth\ForgotPasswordPageController::class)
                ->name('page.auth.forgot-password');

            Route::post('forgot-password', \App\Http\Controllers\Auth\ForgotPasswordActionController::class)
                ->name('auth.forgot-password');

            Route::get('reset-password/{token}', \App\Http\Controllers\Auth\ResetPasswordPageController::class)
                ->name('page.auth.reset-password');

            Route::post('reset-password', \App\Http\Controllers\Auth\ResetPasswordActionController::class)
                ->name('auth.reset-password');
        });

        Route::middleware('auth')->group(function (): void {
            Route::post('logout', \App\Http\Controllers\Auth\LogoutActionController::class)
                ->name('auth.logout');
        });
    });
