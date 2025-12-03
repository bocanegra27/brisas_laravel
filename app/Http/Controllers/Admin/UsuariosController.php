<?php

namespace App\Http\Controllers\Admin;

use App\Services\ApiService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Log;

/**
 * Controlador de Gestión de Usuarios
 * 
 * Maneja todas las operaciones CRUD de usuarios
 * Comunicación con API Spring Boot mediante ApiService
 */
class UsuariosController
{
    private ApiService $apiService;

    public function __construct(ApiService $apiService)
    {
        $this->apiService = $apiService;
    }

    /**
     * Mostrar listado de usuarios con paginación y filtros
     * GET /usuarios
     */
    public function index(Request $request)
    {
        try {
            // Obtener parámetros de búsqueda y filtros
            $page = $request->get('page', 0);
            $size = $request->get('size', 10);
            $rolId = $request->get('rolId');
            $activo = $request->get('activo');

            // Construir query params
            $params = [
                'page' => $page,
                'size' => $size
            ];

            if ($rolId !== null && $rolId !== '') {
                $params['rolId'] = $rolId;
            }

            if ($activo !== null && $activo !== '') {
                $params['activo'] = $activo === 'true' ? 'true' : 'false';
            }

            // Construir URL con query params
            $queryString = http_build_query($params);
            $endpoint = '/usuarios?' . $queryString;

            // Llamada al API con autenticación
            $response = $this->apiService->get($endpoint, [
                'headers' => [
                    'Authorization' => 'Bearer ' . Session::get('jwt_token')
                ]
            ]);

            // Verificar respuesta
            if ($response === null) {
                Log::error('UsuariosController: Error al obtener usuarios del API');
                return view('admin.usuarios.index')->with([
                    'usuarios' => [],
                    'totalElements' => 0,
                    'totalPages' => 0,
                    'currentPage' => 0,
                    'pageSize' => $size,
                    'stats' => [
                        'total' => 0,
                        'activos' => 0,
                        'inactivos' => 0
                    ]
                ]);
            }

            // Obtener estadísticas
            $stats = $this->getEstadisticas();

            // Preparar datos para la vista
            $data = [
                'usuarios' => $response['content'] ?? [],
                'totalElements' => $response['totalElements'] ?? 0,
                'totalPages' => $response['totalPages'] ?? 0,
                'currentPage' => $response['pageable']['pageNumber'] ?? 0,
                'pageSize' => $response['pageable']['pageSize'] ?? $size,
                'stats' => $stats,
                'filtros' => [
                    'rolId' => $rolId,
                    'activo' => $activo
                ]
            ];

            return view('admin.usuarios.index', $data);

        } catch (\Exception $e) {
            Log::error('UsuariosController@index: Excepción', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return view('admin.usuarios.index')->with([
                'usuarios' => [],
                'totalElements' => 0,
                'totalPages' => 0,
                'currentPage' => 0,
                'pageSize' => 10,
                'stats' => [
                    'total' => 0,
                    'activos' => 0,
                    'inactivos' => 0
                ]
            ])->with('error', 'Error al cargar los usuarios. Por favor, intenta nuevamente.');
        }
    }

    /**
     * Mostrar formulario de creación
     * GET /usuarios/crear
     */
    public function crear()
    {
        return view('admin.usuarios.crear');
    }

    /**
     * Guardar nuevo usuario
     * POST /usuarios
     */
    public function store(Request $request)
    {
        try {
            // Validación básica en Laravel
            $validated = $request->validate([
                'nombre' => 'required|min:3|max:100',
                'correo' => 'required|email|max:100',
                'password' => 'required|min:8|max:100',
                'telefono' => 'required|digits:10',
                'docnum' => 'required|max:20',
                'rolId' => 'required|integer|in:1,2,3',
                'tipdocId' => 'required|integer',
                'activo' => 'required|boolean'
            ]);

            // Preparar datos para el API
            $data = [
                'nombre' => $validated['nombre'],
                'correo' => $validated['correo'],
                'password' => $validated['password'],
                'telefono' => $validated['telefono'],
                'docnum' => $validated['docnum'],
                'rolId' => (int) $validated['rolId'],
                'tipdocId' => (int) $validated['tipdocId'],
                'activo' => (bool) $validated['activo']
            ];

            // Llamada al API
            $response = $this->apiService->post('/usuarios', $data, [
                'headers' => [
                    'Authorization' => 'Bearer ' . Session::get('jwt_token')
                ]
            ]);

            // Verificar respuesta
            if ($response === null) {
                Log::warning('UsuariosController@store: API retornó null');
                return back()
                    ->withInput()
                    ->with('error', 'Error al crear el usuario. El correo o documento ya puede estar registrado.');
            }

            Log::info('UsuariosController: Usuario creado exitosamente', [
                'usuario_id' => $response['id'] ?? null
            ]);

            return redirect()
                ->route('admin.usuarios.index')
                ->with('success', '¡Usuario creado exitosamente!');

        } catch (\Illuminate\Validation\ValidationException $e) {
            return back()
                ->withErrors($e->errors())
                ->withInput();
        } catch (\Exception $e) {
            Log::error('UsuariosController@store: Excepción', [
                'error' => $e->getMessage()
            ]);

            return back()
                ->withInput()
                ->with('error', 'Error al crear el usuario. Por favor, verifica los datos.');
        }
    }

    /**
     * Mostrar formulario de edición
     * GET /usuarios/{id}/editar
     */
    public function editar($id)
    {
        try {
            // Obtener usuario del API
            $response = $this->apiService->get("/usuarios/{$id}", [
                'headers' => [
                    'Authorization' => 'Bearer ' . Session::get('jwt_token')
                ]
            ]);

            if ($response === null) {
                return redirect()
                    ->route('admin.usuarios.index')
                    ->with('error', 'Usuario no encontrado.');
            }

            return view('admin.usuarios.editar', [
                'usuario' => $response
            ]);

        } catch (\Exception $e) {
            Log::error('UsuariosController@editar: Excepción', [
                'id' => $id,
                'error' => $e->getMessage()
            ]);

            return redirect()
                ->route('admin.usuarios.index')
                ->with('error', 'Error al cargar el usuario.');
        }
    }

    /**
     * Actualizar usuario existente
     * PUT /usuarios/{id}
     */
    public function update(Request $request, $id)
    {
        try {
            // Validación
            $validated = $request->validate([
                'nombre' => 'required|min:3|max:100',
                'correo' => 'required|email|max:100',
                'telefono' => 'required|digits:10',
                'docnum' => 'required|max:20',
                'rolId' => 'required|integer|in:1,2,3',
                'tipdocId' => 'required|integer',
                'activo' => 'required|boolean'
            ]);

            // Preparar datos (sin password)
            $data = [
                'nombre' => $validated['nombre'],
                'correo' => $validated['correo'],
                'telefono' => $validated['telefono'],
                'docnum' => $validated['docnum'],
                'rolId' => (int) $validated['rolId'],
                'tipdocId' => (int) $validated['tipdocId'],
                'activo' => (bool) $validated['activo']
            ];

            // Llamada al API
            $response = $this->apiService->put("/usuarios/{$id}", $data, [
                'headers' => [
                    'Authorization' => 'Bearer ' . Session::get('jwt_token')
                ]
            ]);

            if ($response === null) {
                return back()
                    ->withInput()
                    ->with('error', 'Error al actualizar el usuario. El correo o documento ya puede estar en uso.');
            }

            Log::info('UsuariosController: Usuario actualizado', ['id' => $id]);

            return redirect()
                ->route('admin.usuarios.index')
                ->with('success', '¡Usuario actualizado exitosamente!');

        } catch (\Illuminate\Validation\ValidationException $e) {
            return back()
                ->withErrors($e->errors())
                ->withInput();
        } catch (\Exception $e) {
            Log::error('UsuariosController@update: Excepción', [
                'id' => $id,
                'error' => $e->getMessage()
            ]);

            return back()
                ->withInput()
                ->with('error', 'Error al actualizar el usuario.');
        }
    }

    /**
     * Activar/Desactivar usuario
     * PATCH /usuarios/{id}/activo
     */
    public function toggleActivo(Request $request, $id)
    {
        try {
            $activo = $request->input('activo', false);
            
            // Verificar que no se desactive a sí mismo
            if (Session::get('user_id') == $id && !$activo) {
                return response()->json([
                    'success' => false,
                    'message' => 'No puedes desactivarte a ti mismo.'
                ], 400);
            }

            // Llamada al API
            $response = $this->apiService->request(
                'PATCH',
                "/usuarios/{$id}/activo?activo=" . ($activo ? 'true' : 'false'),
                null,
                [
                    'headers' => [
                        'Authorization' => 'Bearer ' . Session::get('jwt_token')
                    ]
                ]
            );

            if ($response === null) {
                return response()->json([
                    'success' => false,
                    'message' => 'Error al cambiar el estado del usuario.'
                ], 500);
            }

            Log::info('UsuariosController: Estado cambiado', [
                'id' => $id,
                'activo' => $activo
            ]);

            return response()->json([
                'success' => true,
                'message' => $activo ? 'Usuario activado exitosamente.' : 'Usuario desactivado exitosamente.',
                'activo' => $activo
            ]);

        } catch (\Exception $e) {
            Log::error('UsuariosController@toggleActivo: Excepción', [
                'id' => $id,
                'error' => $e->getMessage()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Error al cambiar el estado del usuario.'
            ], 500);
        }
    }

    /**
     * Eliminar usuario
     * DELETE /usuarios/{id}
     */
    public function eliminar($id)
    {
        try {
            // Verificar que no se elimine a sí mismo
            if (Session::get('user_id') == $id) {
                return response()->json([
                    'success' => false,
                    'message' => 'No puedes eliminar tu propia cuenta.'
                ], 400);
            }

            // Llamada al API
            $response = $this->apiService->delete("/usuarios/{$id}", [
                'headers' => [
                    'Authorization' => 'Bearer ' . Session::get('jwt_token')
                ]
            ]);

            // DELETE retorna 204 No Content, entonces response será un array vacío
            // Si es null, hubo un error
            if ($response === null) {
                return response()->json([
                    'success' => false,
                    'message' => 'Error al eliminar el usuario.'
                ], 500);
            }

            Log::info('UsuariosController: Usuario eliminado', ['id' => $id]);

            return response()->json([
                'success' => true,
                'message' => 'Usuario eliminado permanentemente.'
            ]);

        } catch (\Exception $e) {
            Log::error('UsuariosController@eliminar: Excepción', [
                'id' => $id,
                'error' => $e->getMessage()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Error al eliminar el usuario.'
            ], 500);
        }
    }

    /**
     * Obtener estadísticas de usuarios
     */
    public function getEstadisticas(): array
    {
        try {
            $responseActivos = $this->apiService->get('/usuarios/count?activo=true', [
                'headers' => [
                    'Authorization' => 'Bearer ' . Session::get('jwt_token')
                ]
            ]);

            $responseInactivos = $this->apiService->get('/usuarios/count?activo=false', [
                'headers' => [
                    'Authorization' => 'Bearer ' . Session::get('jwt_token')
                ]
            ]);

            // ✅ Extraer del objeto JSON retornado por Spring Boot
            $activos = $responseActivos['count'] ?? 0;
            $inactivos = $responseInactivos['count'] ?? 0;

            return [
                'total' => $activos + $inactivos,
                'activos' => $activos,
                'inactivos' => $inactivos
            ];

        } catch (\Exception $e) {
            Log::error('UsuariosController@getEstadisticas: Excepción', [
                'error' => $e->getMessage()
            ]);

            return [
                'total' => 0,
                'activos' => 0,
                'inactivos' => 0
            ];
        }
    }
}