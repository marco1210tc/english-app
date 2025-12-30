<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class Authenticate
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        return $next($request);
    }

    protected function redirectTo($request): ?string
    {
        if (! $request->expectsJson()) {
            // todo lo /s/* lo manda al login unificado con rol student
            if (str_starts_with($request->path(), 's/')) {
                return route('login', ['role' => 'student']);
            }
            return route('login');
        }

        return null;
    }
}
