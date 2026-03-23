<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Controllers\Auth\AuthenticatesUsers;
use Illuminate\Http\Request;
use Illuminate\Routing\Controllers\HasMiddleware;
use Illuminate\Routing\Controllers\Middleware;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class LoginController extends Controller implements HasMiddleware
{
    use AuthenticatesUsers;

    public static function middleware(): array
    {
        return [
            new Middleware('guest', except: ['logout']),
        ];
    }

    protected $redirectTo = '/';

    /**
     * Redirect path helper — checks property, then method, then defaults.
     */
    public function redirectPath()
    {
        if (method_exists($this, 'redirectTo')) {
            return $this->redirectTo();
        }

        return property_exists($this, 'redirectTo') ? $this->redirectTo : '/home';
    }

    /**
     * Handle login with optional reCAPTCHA v3 verification.
     *
     * reCAPTCHA is used as a SOFT signal — a failed or missing token does NOT
     * block a legitimate user from logging in. It only blocks obvious bots
     * (token present but score is extremely low, e.g. < 0.1).
     */
    public function login(Request $request)
    {
        // ── Step 1: Basic field validation ─────────────────────────────────
        $request->validate([
            'email'    => ['required', 'email'],
            'password' => ['required', 'string'],
        ]);

        // ── Step 2: reCAPTCHA v3 — SOFT check, never blocks login ──────────
        $recaptchaToken  = $request->input('g-recaptcha-response', '');
        $recaptchaSecret = config('services.recaptcha.secret');

        if (! empty($recaptchaToken) && ! empty($recaptchaSecret)) {
            try {
                $resp    = Http::timeout(5)->asForm()->post('https://www.google.com/recaptcha/api/siteverify', [
                    'secret'   => $recaptchaSecret,
                    'response' => $recaptchaToken,
                    'remoteip' => $request->ip(),
                ]);
                $score   = (float) $resp->json('score', 0.0);
                $success = (bool)  $resp->json('success', false);

                Log::info('reCAPTCHA login check', [
                    'email'   => $request->input('email'),
                    'success' => $success,
                    'score'   => $score,
                    'ip'      => $request->ip(),
                ]);

                if ($success && $score < 0.1) {
                    Log::warning('reCAPTCHA hard block (score < 0.1)', [
                        'email' => $request->input('email'),
                        'score' => $score,
                        'ip'    => $request->ip(),
                    ]);

                    return back()->withErrors([
                        'email' => 'Automated activity detected. Please try again.',
                    ])->withInput($request->only('email'));
                }
            } catch (\Exception $e) {
                Log::warning('reCAPTCHA API unreachable — skipping check', [
                    'error' => $e->getMessage(),
                ]);
            }
        }

        // ── Step 3: Standard Laravel credential check ───────────────────────
        $credentials = $request->only('email', 'password');

        if (Auth::attempt($credentials, $request->filled('remember'))) {
            $request->session()->regenerate();

            return $this->authenticated($request, Auth::user())
                ?: redirect()->intended($this->redirectPath());
        }

        // ── Step 4: Generic failure ──────────────────────────────────────────
        return back()->withErrors([
            'email' => 'The provided credentials do not match our records.',
        ])->withInput($request->only('email'));
    }

    /**
     * Post-login redirect.
     *
     * Admins (admin_access >= 1) go directly to the admin dashboard.
     * Regular users go to the home page.
     *
     * We do NOT use redirect()->intended() for admins because the intended
     * URL could be a protected public page the admin visited before logging
     * in — we always want admins in the control centre, not bounced around.
     */
    protected function authenticated(Request $request, $user)
    {
        if ((int) ($user->admin_access ?? 0) >= 1) {
            return redirect()->route('admin.dashboard');
        }

        return redirect()->intended(route('home'));
    }

    /**
     * Log the user out and clear session data.
     */
    public function logout(Request $request)
    {
        $this->guard()->logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect('/');
    }
}
