<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Password;

class ForgotPasswordController extends Controller
{
    /**
     * Show the forgot password form.
     */
    public function showLinkRequestForm()
    {
        return view('auth.forgot-password');
    }

    /**
     * Send the password reset link email.
     * Rate limited in routes/web.php to 3 attempts per 10 minutes per IP.
     */
    public function sendResetLinkEmail(Request $request)
    {
        $request->validate([
            'email' => ['required', 'email', 'max:255'],
        ]);


        $status = Password::sendResetLink(
            $request->only('email')
        );

        // Always show success message (prevents email enumeration)
        return back()->with('status', __('If an account with that email exists, we\'ve sent a password reset link. Please check your inbox.'));
    }
}
