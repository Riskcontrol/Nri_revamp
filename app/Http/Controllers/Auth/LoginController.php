<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Controllers\Auth\AuthenticatesUsers; // The Engine
use Illuminate\Http\Request;
use Illuminate\Routing\Controllers\HasMiddleware;
use Illuminate\Routing\Controllers\Middleware;

class LoginController extends Controller implements HasMiddleware
{
    use AuthenticatesUsers;

    public static function middleware(): array
    {
        return [
            new Middleware('guest', except: ['logout']),
        ];
    }

   /**
 * Helper method to determine the redirect path.
 */
public function redirectPath()
{
    if (method_exists($this, 'redirectTo')) {
        return $this->redirectTo();
    }

    return property_exists($this, 'redirectTo') ? $this->redirectTo : '/home';
}
    protected $redirectTo = '/location-intelligence/Lagos';


    protected function authenticated(Request $request, $user)
    {
        // If the user is registered but not yet authorized for full data
        if ($user->access_level < 1) {
            return redirect()->route('locationIntelligence', ['state' => 'Lagos'])
                             ->with('show_demo_popup', true);
        }

        // If they are a verified client, just let them in normally
        return redirect()->intended($this->redirectPath());
    }

    /**
     * Log the user out of the application.
     */
    public function logout(Request $request)
    {
        $this->guard()->logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();
        return redirect('/');
    }
}
