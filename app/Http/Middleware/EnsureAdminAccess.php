<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureAdminAccess
{
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        if (! $user || ! $user->canAccessAdmin()) {
            if ($request->expectsJson()) {
                return response()->json(['message' => 'Acceso no autorizado.'], 403);
            }

            return redirect()->route($user ? $user->dashboardRoute() : 'login')
                ->with('error', 'No tienes permisos para acceder al panel de administracion.');
        }

        return $next($request);
    }
}
