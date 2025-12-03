<?php

namespace App\Services\Auth;

use App\Services\ApiService;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Log;

/**
 * Servicio de autenticación contra API Spring Boot
 * 
 * Maneja login, logout y gestión de sesión con JWT
 */
class AuthService
{
    private ApiService $apiService;

    public function __construct(ApiService $apiService)
    {
        $this->apiService = $apiService;
    }

    /**
     * Intenta autenticar usuario contra Spring Boot API
     *
     * @param string $email
     * @param string $password
     * @return array|null Datos del usuario si es exitoso, null si falla
     */
    public function login(string $email, string $password): ?array
    {
        try {
            $response = $this->apiService->post('/auth/login', [
                'email' => $email,
                'password' => $password
            ]);

            // ✅ NUEVO: Verificar si la respuesta es null (ApiService retorna null cuando falla)
            if ($response === null) {
                Log::warning('AuthService: Login falló - API retornó null', [
                    'email' => $email
                ]);
                return null;
            }

            // ✅ NUEVO: Verificar que la respuesta tenga el token
            // Si el API retornó datos pero sin token, el login falló
            if (!isset($response['token']) || empty($response['token'])) {
                Log::warning('AuthService: Login falló - Sin token en respuesta', [
                    'email' => $email,
                    'response_keys' => array_keys($response)
                ]);
                return null;
            }

            // ✅ Login exitoso - $response ya contiene los datos directamente
            // Ya no hay $response['data'], los datos están en $response
            
            // Guardar en sesión de Laravel
            Session::put('jwt_token', $response['token']);
            Session::put('user_role', $response['userRole'] ?? 'ROLE_USUARIO');
            Session::put('user_name', $response['userName'] ?? 'Usuario');
            Session::put('user_email', $response['email'] ?? $email);
            Session::put('user_id', $response['userId'] ?? null);
            Session::put('dashboard_url', $response['dashboardUrl'] ?? '/dashboard');

            Log::info('AuthService: Login exitoso', [
                'user_id' => $response['userId'] ?? null,
                'role' => $response['userRole'] ?? 'ROLE_USUARIO',
                'email' => $email
            ]);

            // Retornar los datos del usuario
            return $response;

        } catch (\Exception $e) {
            Log::error('AuthService: Excepción en login', [
                'email' => $email,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            return null;
        }
    }

    /**
     * Cierra sesión del usuario
     */
    public function logout(): void
    {
        Log::info('AuthService: Logout', [
            'user_id' => Session::get('user_id'),
            'email' => Session::get('user_email')
        ]);

        Session::flush();
        Session::regenerate();
    }

    /**
     * Verifica si hay un usuario autenticado
     */
    public function check(): bool
    {
        return Session::has('jwt_token');
    }

    /**
     * Obtiene el token JWT actual
     */
    public function getToken(): ?string
    {
        return Session::get('jwt_token');
    }

    /**
     * Obtiene datos del usuario autenticado desde sesión
     */
    public function user(): array
    {
        return [
            'id' => Session::get('user_id'),
            'name' => Session::get('user_name'),
            'email' => Session::get('user_email'),
            'role' => Session::get('user_role'),
            'dashboard_url' => Session::get('dashboard_url')
        ];
    }

    /**
     * Verifica si el usuario tiene un rol específico
     */
    public function hasRole(string $role): bool
    {
        $userRole = Session::get('user_role');
        return $userRole === $role;
    }
}