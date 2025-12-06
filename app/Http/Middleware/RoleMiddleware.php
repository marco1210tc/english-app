<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class RoleMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */

    /**
     * Uso:
     *  ->middleware('role:student')
     *  ->middleware('role:teacher')
     *  ->middleware('role:admin')
     *  ->middleware('role:teacher,admin')
     */
    public function handle(Request $request, Closure $next, ...$roles)
    {
        $user = $request->user();

        if (! $user) {
            // No autenticado: lo mandamos al login
            return redirect()->route('login');
        }

        // Si no hay roles definidos en el middleware, dejamos pasar
        if (empty($roles)) {
            return $next($request);
        }

        // Comprobamos que el role del usuario esté en la lista
        if (! in_array($user->role, $roles, true)) {
            // Puedes usar abort(403) o redirigir a otra vista
            abort(403, 'No tienes permiso para acceder a esta sección.');
        }

        return $next($request);
    }
}
