<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Http\Controllers\Auth\RegistersUsers; // Ensure this file exists in this folder
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;
use App\Mail\DemoRequestReceived;

// Required for Laravel 11 Middleware
use Illuminate\Routing\Controllers\HasMiddleware;
use Illuminate\Routing\Controllers\Middleware;

class RegisterController extends Controller implements HasMiddleware
{
    use RegistersUsers;

    /**
     * Fixes the "Call to undefined method middleware()" error.
     * In Laravel 11, we use this static method instead of the constructor.
     */
    public static function middleware(): array
    {
        return [
            new Middleware('guest', except: ['logout']),
        ];
    }

    protected $redirectTo = '/';

    /**
     * Get a validator for an incoming registration request.
     */
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

    /**
     * Create a new user instance after a valid registration.
     */
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
            'access_level' => 0, // Keeps filters locked until demo
        ]);
    }

    /**
     * The user has been registered.
     */
    protected function registered(Request $request, $user)
    {
        // Email logic (Disabled for testing as per your request)
        /*
        try {
            Mail::to($user->email)->send(new DemoRequestReceived($user));
        } catch (\Exception $e) {
            \Log::error("Registration Email Failed: " . $e->getMessage());
        }
        */

        // 2. Redirect back to the Hub with the flash message
        return redirect()->route('/home');
    }
}
