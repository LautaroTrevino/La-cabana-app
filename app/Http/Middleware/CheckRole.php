<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use Illuminate\Support\Facades\Auth;

class CheckRole
{
    public function handle(Request $request, Closure $next, ...$roles): Response
    {
        // 1. Si no está logueado, lo mandamos al login
        if (!Auth::check()) {
            return redirect('login');
        }

        // 2. Obtenemos el rol del usuario
        $userRole = Auth::user()->role;

        // 3. Verificamos si su rol está permitido en esta ruta
        if (in_array($userRole, $roles)) {
            return $next($request);
        }

        // 4. Si no tiene permiso, error 403
        abort(403, 'No tienes permiso para entrar aquí.');
    }
}