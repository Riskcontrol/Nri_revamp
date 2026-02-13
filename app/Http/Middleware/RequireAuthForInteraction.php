<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class RequireAuthForInteraction
{
    public function handle(Request $request, Closure $next)
    {
        // Tier 1 (guest) is blocked from interactive endpoints only
        if (!auth()->check()) {

            // If it's an AJAX/fetch request, return JSON so frontend can show modal
            if ($request->expectsJson() || $request->ajax()) {
                return response()->json([
                    'message' => 'Please register or login to use this feature.',
                    'redirect' => route('register'),
                ], 401);
            }

            // Normal browser request
            return redirect()->route('register');
        }

        return $next($request);
    }
}
