<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Http\Controllers\Auth\RegistersUsers;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Http;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Log;
use Illuminate\Routing\Controllers\HasMiddleware;
use Illuminate\Routing\Controllers\Middleware;

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

            // reCAPTCHA is OPTIONAL at the validation layer.
            // The soft check happens in register() below instead.
            // Removing 'required' here means a missing token no longer
            // causes a hard validation failure.
            'g-recaptcha-response' => ['nullable', 'string'],

            'website' => ['nullable', 'max:0'], // honeypot
        ]);
    }

    /**
     * Override the register method to add soft reCAPTCHA checking
     * before calling the parent create() and login logic.
     */
    public function register(Request $request)
    {
        // ── reCAPTCHA soft check ─────────────────────────────────────────────
        // Same approach as LoginController: never hard-block a real user.
        // Only reject score < 0.1 (clear bots). Log everything else.
        $recaptchaToken  = $request->input('g-recaptcha-response', '');
        $recaptchaSecret = config('services.recaptcha.secret');

        if (! empty($recaptchaToken) && ! empty($recaptchaSecret)) {
            try {
                $resp = Http::timeout(5)->asForm()->post('https://www.google.com/recaptcha/api/siteverify', [
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

                // Only block the absolute clearest bot signals (score < 0.1)
                if ($success && $score < 0.1) {
                    Log::warning('reCAPTCHA hard block on registration (score < 0.1)', [
                        'email' => $request->input('email'),
                        'score' => $score,
                        'ip'    => $request->ip(),
                    ]);

                    return back()
                        ->withErrors(['email' => 'Automated activity detected. Please try again.'])
                        ->withInput($request->except('password', 'password_confirmation'));
                }
            } catch (\Exception $e) {
                // reCAPTCHA API unreachable — log and continue. Never fail a
                // registration because Google's servers are slow or blocked.
                Log::warning('reCAPTCHA API unreachable on registration — skipping check', [
                    'error' => $e->getMessage(),
                ]);
            }
        }

        // ── Standard validation (name, email, password, honeypot) ───────────
        $this->validator($request->all())->validate();

        // ── Create user + fire Registered event + login + redirect ──────────
        $user = $this->create($request->all());

        event(new \Illuminate\Auth\Events\Registered($user));

        $this->guard()->login($user);

        if ($response = $this->registered($request, $user)) {
            return $response;
        }

        return redirect($this->redirectPath());
    }

    /**
     * Create a new user in the database.
     */
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

    /**
     * Handle post-registration logic: send welcome email and redirect home.
     */
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
