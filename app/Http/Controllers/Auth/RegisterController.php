<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Http\Controllers\Auth\RegistersUsers;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Http;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Routing\Controllers\HasMiddleware;
use Illuminate\Routing\Controllers\Middleware;
use Illuminate\Validation\ValidationException;

use App\Mail\WelcomeEmail;

class RegisterController extends Controller implements HasMiddleware
{
    use RegistersUsers;

    public static function middleware(): array
    {
        return [
            new Middleware('guest', except: ['logout']),
        ];
    }

    protected $redirectTo = '/';

    protected function validator(array $data)
    {
        return Validator::make($data, [
            'name'               => ['required', 'string', 'max:255'],
            'email'              => ['required', 'string', 'email', 'max:255', 'unique:users'],
            'organization'       => ['required', 'string', 'max:255'],
            'organization_other' => ['nullable', 'string', 'max:255', 'required_if:organization,Other'],
            'password'           => ['required', 'string', 'min:8', 'confirmed'],
            'g-recaptcha-response' => ['nullable', 'string'],
            'website'            => ['nullable', 'max:0'], // honeypot
        ]);
    }

    public function register(Request $request)
    {
        // ── 1. Honeypot — silent reject ───────────────────────────────────────
        if ($request->filled('website')) {
            return redirect('/');
        }

        // ── 2. IP-based rate limit — 3 registrations per IP per hour ──────────
        $ipKey = 'register:ip:' . $request->ip();

        if (RateLimiter::tooManyAttempts($ipKey, 3)) {
            $seconds = RateLimiter::availableIn($ipKey);

            Log::warning('Registration IP rate limit hit', [
                'ip'      => $request->ip(),
                'email'   => $request->input('email'),
                'seconds' => $seconds,
            ]);

            throw ValidationException::withMessages([
                'email' => 'Too many registration attempts. Please try again in ' . ceil($seconds / 60) . ' minute(s).',
            ]);
        }

        // ── 3. reCAPTCHA v3 — skipped on local, hard-required on production ───
        $recaptchaSecret = config('services.recaptcha.secret');
        $isLocal         = app()->environment('local');

        if (! $isLocal && ! empty($recaptchaSecret)) {
            $recaptchaToken = $request->input('g-recaptcha-response', '');

            if (empty($recaptchaToken)) {
                Log::warning('Registration blocked: missing reCAPTCHA token', [
                    'email' => $request->input('email'),
                    'ip'    => $request->ip(),
                ]);

                throw ValidationException::withMessages([
                    'email' => 'Security check failed. Please refresh the page and try again.',
                ]);
            }

            try {
                $resp    = Http::timeout(5)->asForm()->post('https://www.google.com/recaptcha/api/siteverify', [
                    'secret'   => $recaptchaSecret,
                    'response' => $recaptchaToken,
                    'remoteip' => $request->ip(),
                ]);

                $score   = (float) $resp->json('score', 0.0);
                $success = (bool)  $resp->json('success', false);

                Log::info('reCAPTCHA register check', [
                    'email'   => $request->input('email'),
                    'success' => $success,
                    'score'   => $score,
                    'ip'      => $request->ip(),
                ]);

                // Block only clear bots: token invalid (success:false) OR score below 0.3.
                //
                // Why 0.3 and not 0.5?
                // Real users on Nigerian IPs, fresh browser sessions, or users with
                // privacy extensions routinely score 0.3–0.5. Google's own docs say
                // 0.5 is a "suggested" threshold for low-risk actions — registration
                // with email+password already has its own honeypot + IP rate limiting
                // as additional layers, so we don't need reCAPTCHA to be overly strict.
                // Actual bots score 0.0–0.1 with success:false.
                if (! $success || $score < 0.3) {
                    Log::warning('Registration blocked by reCAPTCHA', [
                        'email'   => $request->input('email'),
                        'score'   => $score,
                        'success' => $success,
                        'ip'      => $request->ip(),
                    ]);

                    RateLimiter::hit($ipKey, 3600);

                    throw ValidationException::withMessages([
                        'email' => 'Security check failed. Please refresh the page and try again.',
                    ]);
                }

                // Borderline score (0.3–0.5): allow through but flag for monitoring
                if ($score < 0.5) {
                    Log::warning('reCAPTCHA borderline score — allowed through', [
                        'email' => $request->input('email'),
                        'score' => $score,
                        'ip'    => $request->ip(),
                    ]);
                }
            } catch (ValidationException $e) {
                throw $e;
            } catch (\Exception $e) {
                // reCAPTCHA API unreachable — log and allow through
                Log::warning('reCAPTCHA API unreachable on registration — allowing through', [
                    'error' => $e->getMessage(),
                ]);
            }
        }

        // ── 4. Standard field validation ──────────────────────────────────────
        $this->validator($request->all())->validate();

        // ── 5. Count against the IP limiter ───────────────────────────────────
        RateLimiter::hit($ipKey, 3600);

        // ── 6. Create user + fire event + login ───────────────────────────────
        $user = $this->create($request->all());

        event(new \Illuminate\Auth\Events\Registered($user));

        $this->guard()->login($user);

        if ($response = $this->registered($request, $user)) {
            return $response;
        }

        return redirect($this->redirectPath());
    }

    protected function create(array $data)
    {
        $org = $data['organization'] ?? null;

        if ($org === 'Other') {
            $org = trim($data['organization_other'] ?? '');
        }

        return User::create([
            'name'         => $data['name'],
            'email'        => $data['email'],
            'organization' => $org,
            'password'     => $data['password'],
            'access_level' => 0,
        ]);
    }

    protected function registered(Request $request, $user)
    {
        try {
            $payload = [
                'first_name' => $user->name,
                'name'       => $user->name,
                'cta_url'    => config('app.url'),
            ];

            Mail::to($user->email)->queue(new WelcomeEmail($payload));
        } catch (\Exception $e) {
            Log::error("Welcome Email Queue Failed for user {$user->id}: " . $e->getMessage());
        }

        return redirect('/');
    }
}
