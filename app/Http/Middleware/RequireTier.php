<?php
// app/Http/Middleware/RequireTier.php
namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class RequireTier
{
    public function handle(Request $request, Closure $next, int $minTier = 2)
    {
        $user = auth()->user();

        // if not logged in, reuse your Tier1 behavior
        if (!$user) {
            if ($request->expectsJson() || $request->ajax()) {
                return response()->json([
                    'message' => 'Please register or login to use this feature.',
                    'redirect' => route('register'),
                ], 401);
            }
            return redirect()->route('register');
        }

        if ((int)$user->tier < $minTier) {
            return response()->json([
                'message' => 'Premium access required for this feature.',
                'upgrade' => true,
            ], 403);
        }

        return $next($request);
    }
}
