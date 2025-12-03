<?php

namespace App\Services\User;

use App\Services\ApiService;

/**
 * Servicio de gestión de usuarios
 * 
 * Maneja operaciones CRUD de usuarios vía API Spring Boot
 */
class UserService
{
    private ApiService $apiService;

    public function __construct(ApiService $apiService)
    {
        $this->apiService = $apiService;
    }

    /**
     * Crea un nuevo usuario (registro público)
     *
     * @param array $userData Datos del usuario
     * @return array ['success' => bool, 'data' => array, 'message' => string]
     */
    public function createUser(array $userData): array
    {
        $response = $this->apiService->post('/usuarios', $userData);

        if ($response['success'] && $response['code'] === 201) {
            return [
                'success' => true,
                'data' => $response['data'],
                'message' => 'Usuario registrado exitosamente'
            ];
        }

        // Extraer mensaje de error del API
        $errorMessage = $response['data']['message'] ?? 'Error al registrar el usuario';

        return [
            'success' => false,
            'data' => [],
            'message' => $errorMessage
        ];
    }

    /**
     * Lista usuarios (requiere autenticación)
     *
     * @param string $token JWT token
     * @param bool|null $activo Filtrar por estado activo
     * @param int|null $rolId Filtrar por rol
     * @return array|null
     */
    public function listUsers(string $token, ?bool $activo = true, ?int $rolId = null): ?array
    {
        $queryParams = [];
        
        if ($activo !== null) {
            $queryParams['activo'] = $activo ? 'true' : 'false';
        }
        
        if ($rolId !== null) {
            $queryParams['rolId'] = $rolId;
        }

        $endpoint = '/usuarios';
        if (!empty($queryParams)) {
            $endpoint .= '?' . http_build_query($queryParams);
        }

        $response = $this->apiService->get($endpoint, $token);

        if ($response['success'] && isset($response['data']['content'])) {
            return $response['data']['content'];
        }

        return null;
    }

    /**
     * Obtiene un usuario por ID
     *
     * @param int $id ID del usuario
     * @param string $token JWT token
     * @return array|null
     */
    public function getUserById(int $id, string $token): ?array
    {
        $response = $this->apiService->get("/usuarios/{$id}", $token);

        if ($response['success'] && $response['code'] === 200) {
            return $response['data'];
        }

        return null;
    }

    /**
     * Actualiza un usuario
     *
     * @param int $id ID del usuario
     * @param array $userData Datos a actualizar
     * @param string $token JWT token
     * @return array
     */
    public function updateUser(int $id, array $userData, string $token): array
    {
        $response = $this->apiService->request('PUT', "/usuarios/{$id}", $userData, $token);

        return [
            'success' => $response['success'] && $response['code'] === 200,
            'data' => $response['data'],
            'message' => $response['success'] ? 'Usuario actualizado' : 'Error al actualizar'
        ];
    }

    /**
     * Cambia el estado de un usuario (activo/inactivo)
     *
     * @param int $id ID del usuario
     * @param bool $activo Nuevo estado
     * @param string $token JWT token
     * @return array
     */
    public function changeUserStatus(int $id, bool $activo, string $token): array
    {
        $endpoint = "/usuarios/{$id}/activo?activo=" . ($activo ? 'true' : 'false');
        $response = $this->apiService->request('PATCH', $endpoint, null, $token);

        return [
            'success' => $response['success'] && $response['code'] === 200,
            'message' => $response['success'] 
                ? 'Estado actualizado correctamente' 
                : 'Error al cambiar el estado'
        ];
    }
}