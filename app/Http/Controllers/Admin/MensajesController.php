<?php

namespace App\Http\Controllers\Admin;

use App\Services\ApiService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Log;

/**
 * Controlador de Gestión de Mensajes/Contactos
 * 
 * Maneja todas las operaciones del módulo de contactos/mensajes
 * Comunicación con API Spring Boot mediante ApiService
 */
class MensajesController
{
    private ApiService $apiService;

    public function __construct(ApiService $apiService)
    {
        $this->apiService = $apiService;
    }

    /**
     * Mostrar listado de mensajes con filtros
     * GET /admin/mensajes
     */
    public function index(Request $request)
    {
        try {
            // Obtener parámetros de filtros
            $via = $request->get('via');
            $estado = $request->get('estado');

            // Construir query params
            $params = [];

            if ($via !== null && $via !== '') {
                $params['via'] = $via;
            }

            if ($estado !== null && $estado !== '') {
                $params['estado'] = $estado;
            }

            // Construir URL con query params
            $queryString = !empty($params) ? '?' . http_build_query($params) : '';
            $endpoint = '/contactos' . $queryString;

            // Llamada al API con autenticación
            $response = $this->apiService->get($endpoint, [
                'headers' => [
                    'Authorization' => 'Bearer ' . Session::get('jwt_token')
                ]
            ]);

            // Verificar respuesta
            if ($response === null) {
                Log::error('MensajesController: Error al obtener mensajes del API');
                return view('admin.mensajes.index')->with([
                    'mensajes' => [],
                    'stats' => [
                        'total' => 0,
                        'pendientes' => 0,
                        'atendidos' => 0,
                        'archivados' => 0
                    ],
                    'filtros' => [
                        'via' => $via,
                        'estado' => $estado
                    ]
                ]);
            }

            // Obtener estadísticas
            $stats = $this->getEstadisticas();

            // Preparar datos para la vista
            $data = [
                'mensajes' => $response,
                'stats' => $stats,
                'filtros' => [
                    'via' => $via,
                    'estado' => $estado
                ]
            ];

            return view('admin.mensajes.index', $data);

        } catch (\Exception $e) {
            Log::error('MensajesController@index: Excepción', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return view('admin.mensajes.index')->with([
                'mensajes' => [],
                'stats' => [
                    'total' => 0,
                    'pendientes' => 0,
                    'atendidos' => 0,
                    'archivados' => 0
                ],
                'filtros' => [
                    'via' => null,
                    'estado' => null
                ]
            ])->with('error', 'Error al cargar los mensajes. Por favor, intenta nuevamente.');
        }
    }

    /**
     * Ver detalles de un mensaje específico
     * GET /admin/mensajes/{id}
     */
    public function ver($id)
    {
        try {
            // Obtener mensaje del API
            $response = $this->apiService->get("/contactos/{$id}", [
                'headers' => [
                    'Authorization' => 'Bearer ' . Session::get('jwt_token')
                ]
            ]);

            if ($response === null) {
                return response()->json([
                    'success' => false,
                    'message' => 'Mensaje no encontrado.'
                ], 404);
            }

            return response()->json([
                'success' => true,
                'mensaje' => $response
            ]);

        } catch (\Exception $e) {
            Log::error('MensajesController@ver: Excepción', [
                'id' => $id,
                'error' => $e->getMessage()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Error al cargar el mensaje.'
            ], 500);
        }
    }

    /**
     * Actualizar mensaje (estado, vía, notas, etc.)
     * PUT /admin/mensajes/{id}
     */
    public function update(Request $request, $id)
    {
        try {
            // Validación
            $validated = $request->validate([
                'estado' => 'required|in:pendiente,atendido,archivado',
                'via' => 'required|in:formulario,whatsapp',
                'notas' => 'nullable|string|max:1000',
                'usuarioId' => 'nullable|integer',
                'usuarioIdAdmin' => 'nullable|integer'
            ]);

            // Preparar datos para el API
            $data = [
                'estado' => $validated['estado'],
                'via' => $validated['via'],
                'notas' => $validated['notas'] ?? null,
                'usuarioId' => isset($validated['usuarioId']) ? (int) $validated['usuarioId'] : null,
                'usuarioIdAdmin' => isset($validated['usuarioIdAdmin']) ? (int) $validated['usuarioIdAdmin'] : null
            ];

            // Llamada al API
            $response = $this->apiService->put("/contactos/{$id}", $data, [
                'headers' => [
                    'Authorization' => 'Bearer ' . Session::get('jwt_token')
                ]
            ]);

            if ($response === null) {
                return response()->json([
                    'success' => false,
                    'message' => 'Error al actualizar el mensaje.'
                ], 500);
            }

            Log::info('MensajesController: Mensaje actualizado', ['id' => $id]);

            return response()->json([
                'success' => true,
                'message' => '¡Mensaje actualizado exitosamente!',
                'mensaje' => $response
            ]);

        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Datos de validación incorrectos.',
                'errors' => $e->errors()
            ], 422);
        } catch (\Exception $e) {
            Log::error('MensajesController@update: Excepción', [
                'id' => $id,
                'error' => $e->getMessage()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Error al actualizar el mensaje.'
            ], 500);
        }
    }

    /**
     * Cambiar estado rápidamente desde el listado
     * PATCH /admin/mensajes/{id}/estado
     */
    public function cambiarEstado(Request $request, $id)
    {
        try {
            $validated = $request->validate([
                'estado' => 'required|in:pendiente,atendido,archivado'
            ]);

            // Obtener mensaje actual
            $mensajeActual = $this->apiService->get("/contactos/{$id}", [
                'headers' => [
                    'Authorization' => 'Bearer ' . Session::get('jwt_token')
                ]
            ]);

            if ($mensajeActual === null) {
                return response()->json([
                    'success' => false,
                    'message' => 'Mensaje no encontrado.'
                ], 404);
            }

            // Actualizar solo el estado, manteniendo los demás datos
            $data = [
                'estado' => $validated['estado'],
                'via' => $mensajeActual['via'],
                'notas' => $mensajeActual['notas'],
                'usuarioId' => $mensajeActual['usuarioId'],
                'usuarioIdAdmin' => Session::get('user_id') // Auto-asignar admin que cambia el estado
            ];

            // Llamada al API
            $response = $this->apiService->put("/contactos/{$id}", $data, [
                'headers' => [
                    'Authorization' => 'Bearer ' . Session::get('jwt_token')
                ]
            ]);

            if ($response === null) {
                return response()->json([
                    'success' => false,
                    'message' => 'Error al cambiar el estado del mensaje.'
                ], 500);
            }

            Log::info('MensajesController: Estado cambiado', [
                'id' => $id,
                'estado' => $validated['estado']
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Estado actualizado exitosamente.',
                'estado' => $validated['estado']
            ]);

        } catch (\Exception $e) {
            Log::error('MensajesController@cambiarEstado: Excepción', [
                'id' => $id,
                'error' => $e->getMessage()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Error al cambiar el estado del mensaje.'
            ], 500);
        }
    }

    /**
     * Eliminar mensaje
     * DELETE /admin/mensajes/{id}
     */
    public function eliminar($id)
    {
        try {
            // Llamada al API
            $response = $this->apiService->delete("/contactos/{$id}", [
                'headers' => [
                    'Authorization' => 'Bearer ' . Session::get('jwt_token')
                ]
            ]);

            // DELETE retorna 204 No Content, entonces response será un array vacío
            // Si es null, hubo un error
            if ($response === null) {
                return response()->json([
                    'success' => false,
                    'message' => 'Error al eliminar el mensaje.'
                ], 500);
            }

            Log::info('MensajesController: Mensaje eliminado', ['id' => $id]);

            return response()->json([
                'success' => true,
                'message' => 'Mensaje eliminado permanentemente.'
            ]);

        } catch (\Exception $e) {
            Log::error('MensajesController@eliminar: Excepción', [
                'id' => $id,
                'error' => $e->getMessage()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Error al eliminar el mensaje.'
            ], 500);
        }
    }

    /**
     * Obtener estadísticas de mensajes
     */
    public function getEstadisticas(): array
    {
        try {
            $responsePendientes = $this->apiService->get('/contactos/count?estado=pendiente', [
                'headers' => [
                    'Authorization' => 'Bearer ' . Session::get('jwt_token')
                ]
            ]);

            $responseAtendidos = $this->apiService->get('/contactos/count?estado=atendido', [
                'headers' => [
                    'Authorization' => 'Bearer ' . Session::get('jwt_token')
                ]
            ]);

            $responseArchivados = $this->apiService->get('/contactos/count?estado=archivado', [
                'headers' => [
                    'Authorization' => 'Bearer ' . Session::get('jwt_token')
                ]
            ]);

            // ✅ Extraer del objeto JSON retornado por Spring Boot
            $pendientes = $responsePendientes['count'] ?? 0;
            $atendidos = $responseAtendidos['count'] ?? 0;
            $archivados = $responseArchivados['count'] ?? 0;

            return [
                'total' => $pendientes + $atendidos + $archivados,
                'pendientes' => $pendientes,
                'atendidos' => $atendidos,
                'archivados' => $archivados
            ];

        } catch (\Exception $e) {
            Log::error('MensajesController@getEstadisticas: Excepción', [
                'error' => $e->getMessage()
            ]);

            return [
                'total' => 0,
                'pendientes' => 0,
                'atendidos' => 0,
                'archivados' => 0
            ];
        }
    }
}