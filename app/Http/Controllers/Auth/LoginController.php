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
     * (token present but score is extremely low, e.g. < 0.3).
     *
     * Rationale: reCAPTCHA v3 runs invisibly. Legitimate users on ad-blockers,
     * corporate networks, or VPNs often get low scores (0.1–0.4) through no
     * fault of their own. Hard-blocking on score < 0.5 locks out real users.
     * Laravel's built-in throttle middleware (throttle:10,1 on the login route)
     * handles brute-force protection reliably without reCAPTCHA gating.
     */
    public function login(Request $request)
    {
        // ── Step 1: Basic field validation ─────────────────────────────────
        $request->validate([
            'email'    => ['required', 'email'],
            'password' => ['required', 'string'],
        ]);

        // ── Step 2: reCAPTCHA v3 — SOFT check, never blocks login ──────────
        //
        // We verify the token if one is present, but we do NOT reject the
        // request based on the result. reCAPTCHA score is logged for monitoring
        // only. Hard blocking is done by route throttling, not score gating.
        $recaptchaToken = $request->input('g-recaptcha-response', '');
        $recaptchaSecret = config('services.recaptcha.secret');

        if (! empty($recaptchaToken) && ! empty($recaptchaSecret)) {
            try {
                $resp  = Http::timeout(5)->asForm()->post('https://www.google.com/recaptcha/api/siteverify', [
                    'secret'   => $recaptchaSecret,
                    'response' => $recaptchaToken,
                    'remoteip' => $request->ip(),
                ]);

                $score   = (float) $resp->json('score', 0.0);
                $success = (bool)  $resp->json('success', false);

                // Log for monitoring — never used to block
                Log::info('reCAPTCHA login check', [
                    'email'   => $request->input('email'),
                    'success' => $success,
                    'score'   => $score,
                    'ip'      => $request->ip(),
                ]);

                // Only block the absolute clearest bot signals (score < 0.1)
                // — this catches automated scripts but not real users on VPNs.
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
                // reCAPTCHA API is unreachable — log and continue to credential check.
                // Never fail a login because Google's servers are slow or blocked.
                Log::warning('reCAPTCHA API unreachable — skipping check', [
                    'error' => $e->getMessage(),
                ]);
            }
        }

        // ── Step 3: Standard Laravel credential check ───────────────────────
        //
        // Auth::attempt() fetches the user by email and runs
        // Hash::check($plainPassword, $storedHash) against the DB value.
        // The 'hashed' cast on User::$password does NOT fire here — casts
        // only run on attribute *writes* (create/save/forceFill), not reads.
        $credentials = $request->only('email', 'password');

        if (Auth::attempt($credentials, $request->filled('remember'))) {
            $request->session()->regenerate(); // Prevent session fixation

            return $this->authenticated($request, Auth::user())
                ?: redirect()->intended($this->redirectPath());
        }

        // ── Step 4: Generic failure — does NOT reveal whether email exists ──
        return back()->withErrors([
            'email' => 'The provided credentials do not match our records.',
        ])->withInput($request->only('email'));
    }

    /**
     * Redirect to home on successful login.
     */
    protected function authenticated(Request $request, $user)
    {
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
