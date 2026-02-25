<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Auth\Events\PasswordReset;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Str;
use Illuminate\Validation\Rules\Password as PasswordRule;

class ResetPasswordController extends Controller
{
    /**
     * Show the password reset form.
     * The token and email come from the link in the user's email.
     */
    public function showResetForm(Request $request, string $token)
    {
        return view('auth.reset-password', [
            'token' => $token,
            'email' => $request->query('email'),
        ]);
    }

    /**
     * Handle the password reset submission.
     */
    public function reset(Request $request)
    {
        $request->validate([
            'token'    => ['required'],
            'email'    => ['required', 'email'],
            'password' => ['required', 'confirmed', PasswordRule::min(8)],
        ]);

        $status = Password::reset(
            $request->only('email', 'password', 'password_confirmation', 'token'),
            function ($user, $password) {
                // Update password with a fresh remember token
                $user->forceFill([
                    'password'       => $password,
                    'remember_token' => Str::random(60),
                ])->save();

                // Dispatch the PasswordReset event (also fires model observers)
                event(new PasswordReset($user));
            }
        );

        if ($status === Password::PASSWORD_RESET) {
            // On success: invalidate all other sessions so old sessions can't remain logged in
            // (Laravel's built-in Password::reset already handles token invalidation)
            return redirect()->route('login')
                ->with('status', __('Your password has been reset successfully. Please log in with your new password.'));
        }

        // On failure: return the specific error
        return back()
            ->withInput($request->only('email'))
            ->withErrors(['email' => __($status)]);
    }
}
