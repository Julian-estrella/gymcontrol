<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureUserRole
{
    /**
     * Handle an incoming request.
     *
     * Verifies that the authenticated user has one of the required roles.
     * If not, redirects to their appropriate dashboard or returns 403.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next, string ...$roles): Response
    {
        $user = $request->user();

        $allowedRoles = array_map('strtolower', $roles);

        if (! $user || ! in_array(strtolower($user->role ?? ''), $allowedRoles, true)) {
            if ($request->expectsJson()) {
                return response()->json(['message' => 'Acceso no autorizado.'], 403);
            }

            // Redirect to appropriate dashboard instead of 403
            return redirect()->route($user ? $user->dashboardRoute() : 'login')
                ->with('error', 'No tienes permisos para acceder a esta sección.');
        }

        return $next($request);
    }
}
