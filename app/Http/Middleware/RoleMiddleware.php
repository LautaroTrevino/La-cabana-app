<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use Illuminate\Support\Facades\Auth;

class RoleMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next, ...$roles): Response
    {
        // 1. Si no está logueado, al Login
        if (!Auth::check()) {
            return redirect('/login');
        }

        // 2. Normalizamos los roles a minúsculas para evitar errores (Admin vs admin)
        $userRole = strtolower(Auth::user()->role);
        $allowedRoles = array_map('strtolower', $roles);

        // 3. Verificamos si tiene permiso
        if (in_array($userRole, $allowedRoles)) {
            return $next($request);
        }

        // 4. Si falla, redirigimos al dashboard con error
        return redirect('/dashboard')->with('error', 'No tienes permiso para acceder a esa sección.');
    }
}