<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class RoleMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next, ...$roles): Response
    {
        // 1. Si el usuario no está logueado, a la calle (Login).
        if (! $request->user()) {
            return redirect('/login');
        }

        // 2. Obtenemos el rol del usuario (convertido a minúsculas para evitar errores)
        $userRole = strtolower($request->user()->role);

        // 3. Verificamos si el rol del usuario está en la lista de permitidos
        // (Convertimos también los roles permitidos a minúsculas)
        $allowedRoles = array_map('strtolower', $roles);

        if (in_array($userRole, $allowedRoles)) {
            return $next($request); // ¡Pase adelante!
        }

        // 4. Si no tiene permiso, lo mandamos al Dashboard con un error (o 403)
        return redirect('/dashboard')->with('error', 'No tienes permisos para entrar ahí.');
    }
}