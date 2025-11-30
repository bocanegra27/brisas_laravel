<?php

namespace App\Services\Auth;

use App\Services\ApiService;
use Illuminate\Support\Facades\Session;

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
        $response = $this->apiService->post('/auth/login', [
            'email' => $email,
            'password' => $password
        ]);

        // Si login exitoso, guardar datos en sesión
        if ($response['success'] && $response['code'] === 200) {
            $userData = $response['data'];
            
            // Guardar en sesión de Laravel
            Session::put('jwt_token', $userData['token']);
            Session::put('user_role', $userData['userRole'] ?? 'ROLE_USUARIO');
            Session::put('user_name', $userData['userName'] ?? 'Usuario');
            Session::put('user_email', $userData['email'] ?? $email);
            Session::put('user_id', $userData['userId'] ?? null);
            Session::put('dashboard_url', $userData['dashboardUrl'] ?? '/dashboard');

            return $userData;
        }

        return null;
    }

    /**
     * Cierra sesión del usuario
     */
    public function logout(): void
    {
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