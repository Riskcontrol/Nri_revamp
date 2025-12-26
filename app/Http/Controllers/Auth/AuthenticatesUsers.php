<?php

namespace App\Http\Controllers\Auth;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

trait AuthenticatesUsers
{
    public function showLoginForm() { return view('auth.login'); }

    public function login(Request $request)
    {
        $credentials = $request->only('email', 'password');

        if (Auth::attempt($credentials, $request->filled('remember'))) {
            $request->session()->regenerate();
            return $this->authenticated($request, Auth::user()) ?: redirect()->intended($this->redirectPath());
        }

        return back()->withErrors(['email' => 'The provided credentials do not match our records.']);
    }

    protected function guard() { return Auth::guard(); }
}
