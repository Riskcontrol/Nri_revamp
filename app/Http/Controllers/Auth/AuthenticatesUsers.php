<?php

namespace App\Http\Controllers\Auth;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

trait AuthenticatesUsers
{
    public function showLoginForm()
    {
        return view('auth.login');
    }

    /**
     * NOTE: This trait's login() method is intentionally removed.
     *
     * LoginController defines its own login() method which fully overrides
     * this trait. Keeping a duplicate login() here was dead code and a
     * maintenance hazard — developers editing the trait version would see
     * no effect in production.
     *
     * All login logic (reCAPTCHA + credential check) lives exclusively in:
     *   app/Http/Controllers/Auth/LoginController::login()
     */

    protected function guard()
    {
        return Auth::guard();
    }
}
