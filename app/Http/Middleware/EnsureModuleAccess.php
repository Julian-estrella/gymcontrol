<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureModuleAccess
{
    public function handle(Request $request, Closure $next, string $module): Response
    {
        $user = $request->user();

        if (! $user || ! $user->canAccessModule($module)) {
            if ($request->expectsJson()) {
                return response()->json(['message' => 'Modulo no autorizado.'], 403);
            }

            return redirect()->route($user && $user->canAccessAdmin() ? 'admin.dashboard' : ($user ? $user->dashboardRoute() : 'login'))
                ->with('error', 'No tienes permisos para acceder a este modulo.');
        }

        return $next($request);
    }
}
