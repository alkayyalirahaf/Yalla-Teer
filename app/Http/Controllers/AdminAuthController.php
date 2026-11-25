<?php

namespace App\Http\Controllers;

use App\Http\Requests\Auth\LoginRequest;
use App\Services\AuthService;
use Illuminate\Support\Facades\RateLimiter;

class AdminAuthController
{
    public function showLogin()
    {
        return view('auth.login', [
            'title' => 'Platform Admin Login',
            'action' => route('admin.login.submit'),
        ]);
    }

    public function login(LoginRequest $request, AuthService $authService)
    {
        $key = 'admin-login:'.sha1($request->input('email').'|'.$request->ip());
        if (RateLimiter::tooManyAttempts($key, 5)) {
            abort(429, 'Too many login attempts. Please try again later.');
        }

        RateLimiter::hit($key, 60);
        if (!$authService->attempt($request->validated(), AuthService::ROLE_SUPER_ADMIN)) {
            return back()->withErrors(['email' => 'These credentials do not match our records.'])->onlyInput('email');
        }

        RateLimiter::clear($key);

        return redirect()->intended(route('admin.dashboard'));
    }
}
