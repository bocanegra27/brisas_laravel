<?php

namespace App\Http\Middleware;

use App\Services\Auth\AuthService;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Middleware de autenticación personalizado
 * 
 * Reemplaza la función requireLogin() del sistema anterior
 * Protege rutas que requieren usuario autenticado
 */
class Authenticate
{
    private AuthService $authService;

    public function __construct(AuthService $authService)
    {
        $this->authService = $authService;
    }

    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next): Response
    {
        // Si no hay usuario autenticado, redirigir a login
        if (!$this->authService->check()) {
            return redirect('/login')
                ->with('error', 'Debes iniciar sesión para acceder a esta página');
        }

        return $next($request);
    }
}