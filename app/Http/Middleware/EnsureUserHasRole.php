<?php

namespace App\Http\Middleware;

use App\Models\Role;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureUserHasRole
{
    /**
     * Handle an incoming request. Require the user to have at least one of the given roles (by slug).
     *
     * @param  array<string>  $roles  Role slugs, e.g. ['super_admin', 'admin']
     */
    public function handle(Request $request, Closure $next, string ...$roles): Response
    {
        if (! $request->user()) {
            return response()->json(['message' => 'Unauthenticated.'], 401);
        }

        $hasRole = $request->user()->roles()->whereIn('slug', $roles)->exists();

        if (! $hasRole) {
            return response()->json(['message' => 'Forbidden. Required role: ' . implode(' or ', $roles)], 403);
        }

        return $next($request);
    }
}
