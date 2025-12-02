<?php

namespace App\Http\Middleware;

use App\Services\Auth\AuthService;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Middleware RedirectIfAuthenticated (guest)
 * 
 * Redirige a usuarios YA autenticados fuera de páginas de login/registro
 * Opuesto del middleware Authenticate
 */
class RedirectIfAuthenticated
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
        // Si YA está autenticado, redirigir a dashboard
        if ($this->authService->check()) {
            $user = $this->authService->user();
            return redirect($user['dashboard_url'] ?? '/dashboard');
        }

        // Si NO está autenticado, dejar pasar
        return $next($request);
    }
}