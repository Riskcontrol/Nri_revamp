<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Http\Controllers\Auth\RegistersUsers;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;

use App\Mail\WelcomeEmail; // ✅ use welcome email

use Illuminate\Routing\Controllers\HasMiddleware;
use Illuminate\Routing\Controllers\Middleware;

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
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:users'],
            'organization' => ['required', 'string', 'max:255'],
            'organization_other' => ['nullable', 'string', 'max:255', 'required_if:organization,Other'],
            'password' => ['required', 'string', 'min:8', 'confirmed'],
        ]);
    }

    protected function create(array $data)
    {
        $org = $data['organization'] ?? null;

        if ($org === 'Other') {
            $org = trim($data['organization_other'] ?? '');
        }

        return User::create([
            'name' => $data['name'],
            'email' => $data['email'],
            'organization' => $org,
            'password' => Hash::make($data['password']),
            'access_level' => 0,
        ]);
    }

    protected function registered(Request $request, $user)
    {
        try {
            $payload = [
                'first_name' => $user->name, // (optional) split if you want first name only
                'name' => $user->name,
                'cta_url' => config('app.url'),
            ];

            Mail::to($user->email)->send(new WelcomeEmail($payload));
        } catch (\Exception $e) {
            \Log::error("Welcome Email Failed: " . $e->getMessage());
        }

        // ✅ FIX: use a named route OR a URL redirect

        // If your route name is "home"
        // return redirect()->route('home');

        // If you just want /home URL
        return redirect('/');
    }
}
