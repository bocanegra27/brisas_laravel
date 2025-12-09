<?php

namespace App\Services\User;

use App\Services\ApiService;
use Illuminate\Support\Facades\Log;

/**
 * Servicio de gestión de usuarios
 * 
 * Maneja operaciones CRUD de usuarios vía API Spring Boot
 *  CORREGIDO: Ahora consistente con AuthService y ApiService
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
     * @return array ['success' => bool, 'data' => array|null, 'message' => string]
     */
    public function createUser(array $userData): array
    {
        try {
            //  ApiService retorna null si falla, o los datos del API si es exitoso
            $response = $this->apiService->post('/usuarios', $userData);

            //  Verificar si la respuesta es null (igual que en AuthService)
            if ($response === null) {
                Log::warning('UserService: Registro falló - API retornó null', [
                    'correo' => $userData['correo'] ?? 'desconocido'
                ]);

                return [
                    'success' => false,
                    'data' => null,
                    'message' => 'El correo electrónico o documento ya está registrado.'
                ];
            }

            //  Registro exitoso - $response contiene directamente los datos del usuario
            Log::info('UserService: Usuario creado exitosamente', [
                'usuario_id' => $response['id'] ?? null,
                'correo' => $response['correo'] ?? 'desconocido'
            ]);

            return [
                'success' => true,
                'data' => $response,
                'message' => 'Usuario registrado exitosamente'
            ];

        } catch (\Exception $e) {
            Log::error('UserService: Excepción en createUser', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return [
                'success' => false,
                'data' => null,
                'message' => 'Error al registrar el usuario. Por favor, intenta nuevamente.'
            ];
        }
    }

    /**
     * Lista usuarios (requiere autenticación)
     *
     * @param string $token JWT token
     * @param bool|null $activo Filtrar por estado activo
     * @param int|null $rolId Filtrar por rol
     * @param int $page Número de página
     * @param int $size Tamaño de página
     * @return array|null
     */
    public function listUsers(
        string $token, 
        ?bool $activo = null, 
        ?int $rolId = null,
        int $page = 0,
        int $size = 10
    ): ?array
    {
        try {
            $queryParams = [
                'page' => $page,
                'size' => $size
            ];
            
            if ($activo !== null) {
                $queryParams['activo'] = $activo ? 'true' : 'false';
            }
            
            if ($rolId !== null) {
                $queryParams['rolId'] = $rolId;
            }

            $endpoint = '/usuarios?' . http_build_query($queryParams);

            //  ApiService recibe el token en opciones, igual que en otros controladores
            $response = $this->apiService->get($endpoint, [
                'headers' => [
                    'Authorization' => 'Bearer ' . $token
                ]
            ]);

            //  Si response es null, retornar null
            if ($response === null) {
                Log::warning('UserService: Error al listar usuarios');
                return null;
            }

            //  Retornar directamente la respuesta
            return $response;

        } catch (\Exception $e) {
            Log::error('UserService: Excepción en listUsers', [
                'error' => $e->getMessage()
            ]);
            return null;
        }
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
        try {
            $response = $this->apiService->get("/usuarios/{$id}", [
                'headers' => [
                    'Authorization' => 'Bearer ' . $token
                ]
            ]);

            //  Si response es null, retornar null
            if ($response === null) {
                Log::warning('UserService: Usuario no encontrado', ['id' => $id]);
                return null;
            }

            //  Retornar directamente los datos del usuario
            return $response;

        } catch (\Exception $e) {
            Log::error('UserService: Excepción en getUserById', [
                'id' => $id,
                'error' => $e->getMessage()
            ]);
            return null;
        }
    }

    /**
     * Actualiza un usuario
     *
     * @param int $id ID del usuario
     * @param array $userData Datos a actualizar
     * @param string $token JWT token
     * @return array ['success' => bool, 'data' => array|null, 'message' => string]
     */
    public function updateUser(int $id, array $userData, string $token): array
    {
        try {
            $response = $this->apiService->put("/usuarios/{$id}", $userData, [
                'headers' => [
                    'Authorization' => 'Bearer ' . $token
                ]
            ]);

            //  Verificar si la actualización fue exitosa
            if ($response === null) {
                Log::warning('UserService: Error al actualizar usuario', ['id' => $id]);

                return [
                    'success' => false,
                    'data' => null,
                    'message' => 'Error al actualizar el usuario. El correo o documento puede estar duplicado.'
                ];
            }

            //  Actualización exitosa
            Log::info('UserService: Usuario actualizado', ['id' => $id]);

            return [
                'success' => true,
                'data' => $response,
                'message' => 'Usuario actualizado correctamente'
            ];

        } catch (\Exception $e) {
            Log::error('UserService: Excepción en updateUser', [
                'id' => $id,
                'error' => $e->getMessage()
            ]);

            return [
                'success' => false,
                'data' => null,
                'message' => 'Error al actualizar el usuario.'
            ];
        }
    }

    /**
     * Cambia el estado de un usuario (activo/inactivo)
     *
     * @param int $id ID del usuario
     * @param bool $activo Nuevo estado
     * @param string $token JWT token
     * @return array ['success' => bool, 'message' => string]
     */
    public function changeUserStatus(int $id, bool $activo, string $token): array
    {
        try {
            $endpoint = "/usuarios/{$id}/activo?activo=" . ($activo ? 'true' : 'false');
            
            $response = $this->apiService->request('PATCH', $endpoint, null, [
                'headers' => [
                    'Authorization' => 'Bearer ' . $token
                ]
            ]);

            //  Verificar resultado
            if ($response === null) {
                Log::warning('UserService: Error al cambiar estado', [
                    'id' => $id,
                    'activo' => $activo
                ]);

                return [
                    'success' => false,
                    'message' => 'Error al cambiar el estado del usuario.'
                ];
            }

            //  Cambio exitoso
            Log::info('UserService: Estado cambiado', [
                'id' => $id,
                'activo' => $activo
            ]);

            return [
                'success' => true,
                'message' => $activo ? 'Usuario activado correctamente' : 'Usuario desactivado correctamente'
            ];

        } catch (\Exception $e) {
            Log::error('UserService: Excepción en changeUserStatus', [
                'id' => $id,
                'error' => $e->getMessage()
            ]);

            return [
                'success' => false,
                'message' => 'Error al cambiar el estado del usuario.'
            ];
        }
    }

    /**
     * Elimina un usuario permanentemente
     *
     * @param int $id ID del usuario
     * @param string $token JWT token
     * @return array ['success' => bool, 'message' => string]
     */
    public function deleteUser(int $id, string $token): array
    {
        try {
            $response = $this->apiService->delete("/usuarios/{$id}", [
                'headers' => [
                    'Authorization' => 'Bearer ' . $token
                ]
            ]);

            //  DELETE retorna array vacío en éxito, null en error
            if ($response === null) {
                Log::warning('UserService: Error al eliminar usuario', ['id' => $id]);

                return [
                    'success' => false,
                    'message' => 'Error al eliminar el usuario.'
                ];
            }

            //  Eliminación exitosa
            Log::info('UserService: Usuario eliminado', ['id' => $id]);

            return [
                'success' => true,
                'message' => 'Usuario eliminado permanentemente.'
            ];

        } catch (\Exception $e) {
            Log::error('UserService: Excepción en deleteUser', [
                'id' => $id,
                'error' => $e->getMessage()
            ]);

            return [
                'success' => false,
                'message' => 'Error al eliminar el usuario.'
            ];
        }
    }

    /**
     * Verifica si un correo ya existe en el sistema
     *
     * @param string $correo
     * @return bool
     */
    public function emailExists(string $correo): bool
    {
        try {
            $response = $this->apiService->get(
                "/usuarios/existe?correo=" . urlencode($correo)
            );

            return $response['existe'] ?? false;

        } catch (\Exception $e) {
            Log::error('UserService: Error verificando email', [
                'correo' => $correo,
                'error' => $e->getMessage()
            ]);

            return false;
        }
    }

    /**
     * Verifica si un documento ya existe en el sistema
     *
     * @param string $docnum
     * @return bool
     */
    public function documentExists(string $docnum): bool
    {
        try {
            $response = $this->apiService->get(
                "/usuarios/existe?docnum=" . urlencode($docnum)
            );

            return $response['existe'] ?? false;

        } catch (\Exception $e) {
            Log::error('UserService: Error verificando documento', [
                'docnum' => $docnum,
                'error' => $e->getMessage()
            ]);

            return false;
        }
    }
}