<?php

namespace App\Http\Controllers\Admin;

use App\Services\ApiService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Log;

/**
 * ✨ CONTROLADOR MEJORADO: Gestión de Mensajes/Contactos
 * 
 * NUEVAS FUNCIONALIDADES:
 * - Tipo de cliente (anónimo/registrado/externo)
 * - Filtro por personalización vinculada
 * - Ver personalización inline
 * - Botón crear pedido para TODOS los mensajes
 */
class MensajesController
{
    private ApiService $apiService;

    public function __construct(ApiService $apiService)
    {
        $this->apiService = $apiService;
    }

    /**
     * 🔥 MEJORADO: Mostrar listado con tipo de cliente y personalización
     * GET /admin/mensajes
     */
    public function index(Request $request)
    {
        try {
            // Obtener parámetros de filtros
            $tipoCliente = $request->get('tipoCliente'); // anónimo, registrado, externo
            $estado = $request->get('estado');
            $tienePersonalizacion = $request->get('tienePersonalizacion'); // true/false

            // Construir query params (solo estado va al backend)
            $params = [];
            if ($estado !== null && $estado !== '') {
                $params['estado'] = $estado;
            }

            $queryString = !empty($params) ? '?' . http_build_query($params) : '';
            $endpoint = '/contactos' . $queryString;

            // Llamada al API
            $response = $this->apiService->get($endpoint, [
                'headers' => ['Authorization' => 'Bearer ' . Session::get('jwt_token')]
            ]);

            if ($response === null) {
                Log::error('MensajesController: Error al obtener mensajes del API');
                return view('admin.mensajes.index')->with([
                    'mensajes' => [],
                    'stats' => $this->getEstadisticasVacias(),
                    'filtros' => $this->getFiltrosDefault($tipoCliente, $estado, $tienePersonalizacion)
                ]);
            }

            // 🔥 Enriquecer mensajes con tipoCliente y personalización
            $mensajesEnriquecidos = array_map(function($mensaje) {
                return $this->enriquecerMensaje($mensaje);
            }, $response);

            // Aplicar filtros locales
            $mensajesFiltrados = $this->aplicarFiltros(
                $mensajesEnriquecidos, 
                $tipoCliente, 
                $tienePersonalizacion
            );

            // Estadísticas
            $stats = $this->getEstadisticas();

            return view('admin.mensajes.index', [
                'mensajes' => array_values($mensajesFiltrados),
                'stats' => $stats,
                'filtros' => $this->getFiltrosDefault($tipoCliente, $estado, $tienePersonalizacion)
            ]);

        } catch (\Exception $e) {
            Log::error('MensajesController@index: Excepción', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return view('admin.mensajes.index')->with([
                'mensajes' => [],
                'stats' => $this->getEstadisticasVacias(),
                'filtros' => $this->getFiltrosDefault(null, null, null)
            ])->with('error', 'Error al cargar los mensajes. Por favor, intenta nuevamente.');
        }
    }

    /**
     * 🔥 NUEVO: Ver mensaje CON personalización vinculada
     * GET /admin/mensajes/{id}/con-personalizacion
     */
    public function verConPersonalizacion($id)
    {
        try {
            $response = $this->apiService->get("/contactos/{$id}/con-personalizacion", [
                'headers' => ['Authorization' => 'Bearer ' . Session::get('jwt_token')]
            ]);

            if ($response === null) {
                return response()->json([
                    'success' => false,
                    'message' => 'Mensaje no encontrado.'
                ], 404);
            }

            // Enriquecer contacto
            $contactoEnriquecido = $this->enriquecerMensaje($response['contacto']);

            return response()->json([
                'success' => true,
                'contacto' => $contactoEnriquecido,
                'personalizacion' => $response['personalizacion'] ?? null
            ]);

        } catch (\Exception $e) {
            Log::error('MensajesController@verConPersonalizacion: Excepción', [
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
     * Ver detalles básicos de un mensaje
     * GET /admin/mensajes/{id}
     */
    public function ver($id)
    {
        try {
            $response = $this->apiService->get("/contactos/{$id}", [
                'headers' => ['Authorization' => 'Bearer ' . Session::get('jwt_token')]
            ]);

            if ($response === null) {
                return response()->json([
                    'success' => false,
                    'message' => 'Mensaje no encontrado.'
                ], 404);
            }

            // Enriquecer
            $mensajeEnriquecido = $this->enriquecerMensaje($response);

            return response()->json([
                'success' => true,
                'mensaje' => $mensajeEnriquecido
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
            $validated = $request->validate([
                'estado' => 'required|in:pendiente,atendido,archivado',
                'via' => 'required|in:formulario,whatsapp',
                'notas' => 'nullable|string|max:1000',
                'usuarioId' => 'nullable|integer',
                'usuarioIdAdmin' => 'nullable|integer'
            ]);

            $data = [
                'estado' => $validated['estado'],
                'via' => $validated['via'],
                'notas' => $validated['notas'] ?? null,
                'usuarioId' => isset($validated['usuarioId']) ? (int) $validated['usuarioId'] : null,
                'usuarioIdAdmin' => isset($validated['usuarioIdAdmin']) ? (int) $validated['usuarioIdAdmin'] : null
            ];

            $response = $this->apiService->put("/contactos/{$id}", $data, [
                'headers' => ['Authorization' => 'Bearer ' . Session::get('jwt_token')]
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
                'mensaje' => $this->enriquecerMensaje($response)
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
     * Cambiar estado rápidamente
     * PATCH /admin/mensajes/{id}/estado
     */
    public function cambiarEstado(Request $request, $id)
    {
        try {
            $validated = $request->validate([
                'estado' => 'required|in:pendiente,atendido,archivado'
            ]);

            $mensajeActual = $this->apiService->get("/contactos/{$id}", [
                'headers' => ['Authorization' => 'Bearer ' . Session::get('jwt_token')]
            ]);

            if ($mensajeActual === null) {
                return response()->json([
                    'success' => false,
                    'message' => 'Mensaje no encontrado.'
                ], 404);
            }

            $data = [
                'estado' => $validated['estado'],
                'via' => $mensajeActual['via'],
                'notas' => $mensajeActual['notas'],
                'usuarioId' => $mensajeActual['usuarioId'],
                'usuarioIdAdmin' => Session::get('user_id')
            ];

            $response = $this->apiService->put("/contactos/{$id}", $data, [
                'headers' => ['Authorization' => 'Bearer ' . Session::get('jwt_token')]
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
            $response = $this->apiService->delete("/contactos/{$id}", [
                'headers' => ['Authorization' => 'Bearer ' . Session::get('jwt_token')]
            ]);

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
            $token = Session::get('jwt_token');
            $headers = ['headers' => ['Authorization' => 'Bearer ' . $token]];

            $responsePendientes = $this->apiService->get('/contactos/count?estado=pendiente', $headers);
            $responseAtendidos = $this->apiService->get('/contactos/count?estado=atendido', $headers);
            $responseArchivados = $this->apiService->get('/contactos/count?estado=archivado', $headers);

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

            return $this->getEstadisticasVacias();
        }
    }

    // ============================================
    // 🔥 MÉTODOS PRIVADOS AUXILIARES
    // ============================================

    /**
     * Enriquece un mensaje con tipoCliente y personalización
     * RESPETA lo que el backend ya calculó
     */
    private function enriquecerMensaje(array $mensaje): array
    {
        // ✅ Si el backend YA calculó tipoCliente, respetarlo
        if (!isset($mensaje['tipoCliente']) || empty($mensaje['tipoCliente'])) {
            // Solo calcular si no viene del backend
            if (!empty($mensaje['usuarioId']) && $mensaje['usuarioId'] !== 0) {
                $mensaje['tipoCliente'] = 'registrado';
            } elseif (!empty($mensaje['sesionId']) && $mensaje['sesionId'] !== 0) {
                $mensaje['tipoCliente'] = 'anonimo';
            } else {
                $mensaje['tipoCliente'] = 'externo';
            }
        }
        
        // ✅ Si el backend YA calculó tienePersonalizacion, respetarlo
        if (!isset($mensaje['tienePersonalizacion'])) {
            $mensaje['tienePersonalizacion'] = !empty($mensaje['personalizacionId']) && $mensaje['personalizacionId'] !== 0;
        }
        
        return $mensaje;
    }

    /**
     * Aplicar filtros locales (frontend)
     */
    private function aplicarFiltros(array $mensajes, $tipoCliente, $tienePersonalizacion): array
    {
        if ($tipoCliente !== null && $tipoCliente !== '') {
            $mensajes = array_filter($mensajes, function($msg) use ($tipoCliente) {
                return ($msg['tipoCliente'] ?? 'externo') === $tipoCliente;
            });
        }

        if ($tienePersonalizacion !== null && $tienePersonalizacion !== '') {
            $tienePers = $tienePersonalizacion === 'true';
            $mensajes = array_filter($mensajes, function($msg) use ($tienePers) {
                return ($msg['tienePersonalizacion'] ?? false) === $tienePers;
            });
        }

        return $mensajes;
    }

    /**
     * Obtener filtros por defecto
     */
    private function getFiltrosDefault($tipoCliente, $estado, $tienePersonalizacion): array
    {
        return [
            'tipoCliente' => $tipoCliente,
            'estado' => $estado,
            'tienePersonalizacion' => $tienePersonalizacion
        ];
    }

    /**
     * Estadísticas vacías
     */
    private function getEstadisticasVacias(): array
    {
        return [
            'total' => 0,
            'pendientes' => 0,
            'atendidos' => 0,
            'archivados' => 0
        ];
    }
}