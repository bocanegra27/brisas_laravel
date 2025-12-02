<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Log;
use Symfony\Component\HttpFoundation\Response;

class CheckRole
{
    /**
     * Handle an incoming request.
     *
     * IMPORTANTE: Este middleware NO verifica autenticación
     * Solo verifica que el usuario tenga el rol correcto
     * La autenticación ya fue verificada por auth.custom
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     * @param  string  ...$roles
     */
    public function handle(Request $request, Closure $next, string ...$roles): Response
    {
        // ✅ SOLO verificamos el rol (auth.custom ya verificó la autenticación)
        $userRole = Session::get('user_role');

        // Si no hay rol (lo cual es raro si auth.custom funcionó), redirigir
        if (!$userRole) {
            Log::error('CheckRole: Usuario autenticado sin rol en sesión', [
                'url' => $request->fullUrl(),
                'session_data' => Session::all()
            ]);
            
            return redirect('/login')
                ->with('error', 'Error en la sesión. Por favor, vuelve a iniciar sesión.');
        }

        // Normalizar roles requeridos
        $normalizedRoles = $this->normalizeRoles($roles);

        // Log en desarrollo
        if (config('app.debug')) {
            Log::info('CheckRole verificando', [
                'user_role' => $userRole,
                'required_roles' => $normalizedRoles,
                'url' => $request->fullUrl()
            ]);
        }

        // Verificar si el rol del usuario está permitido
        if (!in_array($userRole, $normalizedRoles)) {
            $dashboardUrl = $this->getDashboardForRole($userRole);
            
            Log::warning('CheckRole: Acceso denegado', [
                'user_role' => $userRole,
                'required_roles' => $normalizedRoles,
                'redirect_to' => $dashboardUrl
            ]);
            
            return redirect($dashboardUrl)
                ->with('error', 'No tienes permisos para acceder a esta página');
        }

        // ✅ Rol correcto, continuar
        return $next($request);
    }

    /**
     * Normaliza los roles para el formato de la aplicación
     * 
     * @param array $roles
     * @return array
     */
    private function normalizeRoles(array $roles): array
    {
        $roleMap = [
            'admin' => 'ROLE_ADMINISTRADOR',
            'designer' => 'ROLE_DISEÑADOR',
            'user' => 'ROLE_USUARIO',
            'ROLE_ADMINISTRADOR' => 'ROLE_ADMINISTRADOR',
            'ROLE_DISEÑADOR' => 'ROLE_DISEÑADOR',
            'ROLE_USUARIO' => 'ROLE_USUARIO',
        ];

        $normalized = [];
        foreach ($roles as $role) {
            if (isset($roleMap[$role])) {
                $normalized[] = $roleMap[$role];
            } else {
                Log::warning("CheckRole: Rol desconocido '{$role}'");
            }
        }

        return $normalized;
    }

    /**
     * Obtiene la URL del dashboard según el rol
     * 
     * @param string $role
     * @return string
     */
    private function getDashboardForRole(string $role): string
    {
        return match($role) {
            'ROLE_ADMINISTRADOR' => '/admin/dashboard',
            'ROLE_DISEÑADOR' => '/designer/dashboard',
            'ROLE_USUARIO' => '/user/dashboard',
            default => '/dashboard'
        };
    }
}