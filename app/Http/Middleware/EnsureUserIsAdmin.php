<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

/**
 * EnsureUserIsAdmin
 *
 * A dedicated named middleware that gates any route to users with
 * admin_access >= 1.  Register it in bootstrap/app.php under the alias
 * 'admin' so routes can use ->middleware('admin') cleanly.
 *
 * Using a named middleware in addition to the Gate 'can:admin-access'
 * approach means we have a single, explicit, testable class for admin
 * protection — rather than relying on the implicit Gate evaluation.
 *
 * Registration (bootstrap/app.php):
 *   $middleware->alias([
 *       'admin' => \App\Http\Middleware\EnsureUserIsAdmin::class,
 *   ]);
 */
class EnsureUserIsAdmin
{
    public function handle(Request $request, Closure $next)
    {
        // Must be authenticated first
        if (! Auth::check()) {
            if ($request->expectsJson()) {
                return response()->json(['message' => 'Unauthenticated.'], 401);
            }

            return redirect()->route('login')->with(
                'error',
                'You must be logged in to access this area.'
            );
        }

        // Must have admin_access >= 1
        if ((int) Auth::user()->admin_access < 1) {
            if ($request->expectsJson()) {
                return response()->json(['message' => 'Forbidden.'], 403);
            }

            // Redirect to home with a generic message — do not reveal that
            // an admin area exists, to avoid enumeration.
            abort(403, 'You do not have permission to access this area.');
        }

        return $next($request);
    }
}
