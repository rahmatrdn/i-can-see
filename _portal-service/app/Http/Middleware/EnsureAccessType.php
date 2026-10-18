<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureAccessType
{
    /**
     * Handle an incoming request.
     * Usage on routes: ->middleware('access_type:1,2,3')
     *
     * @param  string  ...$allowedTypes  Allowed access_type values (from UserConst)
     */
    public function handle(Request $request, Closure $next, string ...$allowedTypes): Response
    {
        $user = $request->user();

        if (! $user || ! in_array((string) $user->access_type, $allowedTypes)) {
            if ($request->expectsJson()) {
                return response()->json(['message' => 'Forbidden.'], 403);
            }

            abort(403, 'Anda tidak memiliki akses ke halaman ini.');
        }

        return $next($request);
    }
}
