<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Middleware para prevenir caché del navegador en páginas autenticadas
 * 
 * Esto evita que al presionar "Atrás" después de logout,
 * el navegador muestre la página cacheada
 */
class PreventBackHistory
{
    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next): Response
    {
        $response = $next($request);

        // Agregar headers para prevenir caché
        return $response->header('Cache-Control', 'no-cache, no-store, must-revalidate')
                        ->header('Pragma', 'no-cache')
                        ->header('Expires', '0');
    }
}