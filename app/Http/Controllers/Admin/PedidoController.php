<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Services\ApiService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Log;

/**
 * Controlador de Gestion de Pedidos
 * 
 * Maneja todas las operaciones CRUD de pedidos
 * Comunicacion con API Spring Boot mediante ApiService
 */
class PedidoController extends Controller
{
    private ApiService $apiService;

    public function __construct(ApiService $apiService)
    {
        $this->apiService = $apiService;
    }

    /**
     * Mostrar listado de pedidos con paginacion y filtros
     * GET /admin/pedidos
     */
    public function index(Request $request)
    {
        try {
            // Obtener parametros de busqueda y filtros
            $page = $request->get('page', 0);
            $size = $request->get('size', 10);
            $estadoId = $request->get('estadoId');
            $codigo = $request->get('codigo');

            // Construir query params
            $params = [
                'page' => $page,
                'size' => $size
            ];

            if ($estadoId !== null && $estadoId !== '') {
                $params['estadoId'] = $estadoId;
            }

            if ($codigo !== null && $codigo !== '') {
                $params['codigo'] = $codigo;
            }

            // Construir URL con query params
            $queryString = http_build_query($params);
            $endpoint = '/pedidos?' . $queryString;

            // Llamada al API con autenticacion
            $response = $this->apiService->get($endpoint, [
                'headers' => [
                    'Authorization' => 'Bearer ' . Session::get('jwt_token')
                ]
            ]);

            // Verificar respuesta
            if ($response === null) {
                Log::error('PedidoController: Error al obtener pedidos del API');
                return view('admin.pedidos.index')->with([
                    'pedidos' => [],
                    'totalElements' => 0,
                    'totalPages' => 0,
                    'currentPage' => 0,
                    'pageSize' => $size,
                    'stats' => $this->getEstadisticasVacias(),
                    'estados' => $this->getEstadosDisponibles()
                ]);
            }

            // CORRECCION: Detectar si es array simple o objeto paginado
            $pedidos = [];
            $totalElements = 0;
            $totalPages = 0;
            $currentPage = 0;
            $pageSize = $size;

            if (isset($response['content']) && is_array($response['content'])) {
                // Respuesta paginada de Spring Boot
                $pedidos = $response['content'];
                $totalElements = $response['totalElements'] ?? count($pedidos);
                $totalPages = $response['totalPages'] ?? 1;
                $currentPage = $response['pageable']['pageNumber'] ?? 0;
                $pageSize = $response['pageable']['pageSize'] ?? $size;
            } elseif (is_array($response)) {
                // Array simple de pedidos
                $pedidos = $response;
                $totalElements = count($pedidos);
                $totalPages = (int) ceil($totalElements / $size);
                $currentPage = $page;
                $pageSize = $size;
            }

            // Enriquecer pedidos con informacion procesada
            $pedidos = array_map(function($pedido) {
                return $this->enriquecerPedido($pedido);
            }, $pedidos);

            // Obtener estadisticas
            $stats = $this->getEstadisticas();
            
            // Obtener lista de estados disponibles
            $estados = $this->getEstadosDisponibles();

            // Preparar datos para la vista
            $data = [
                'pedidos' => $pedidos,
                'totalElements' => $totalElements,
                'totalPages' => $totalPages,
                'currentPage' => $currentPage,
                'pageSize' => $pageSize,
                'stats' => $stats,
                'estados' => $estados,
                'filtros' => [
                    'estadoId' => $estadoId,
                    'codigo' => $codigo
                ]
            ];

            return view('admin.pedidos.index', $data);

        } catch (\Exception $e) {
            Log::error('PedidoController@index: Excepcion', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return view('admin.pedidos.index')->with([
                'pedidos' => [],
                'totalElements' => 0,
                'totalPages' => 0,
                'currentPage' => 0,
                'pageSize' => 10,
                'stats' => $this->getEstadisticasVacias(),
                'estados' => $this->getEstadosDisponibles()
            ])->with('error', 'Error al cargar los pedidos. Por favor, intenta nuevamente.');
        }
    }

    /**
     * Ver detalles de un pedido especifico
     * GET /admin/pedidos/{id}
     */
    public function ver($id)
    {
        try {
            $response = $this->apiService->get("/pedidos/{$id}", [
                'headers' => [
                    'Authorization' => 'Bearer ' . Session::get('jwt_token')
                ]
            ]);

            if ($response === null) {
                return redirect()
                    ->route('admin.pedidos.index')
                    ->with('error', 'Pedido no encontrado.');
            }

            // Enriquecer pedido
            $pedido = $this->enriquecerPedido($response);

            return view('admin.pedidos.ver', [
                'pedido' => $pedido
            ]);

        } catch (\Exception $e) {
            Log::error('PedidoController@ver: Excepcion', [
                'id' => $id,
                'error' => $e->getMessage()
            ]);

            return redirect()
                ->route('admin.pedidos.index')
                ->with('error', 'Error al cargar el pedido.');
        }
    }

    /**
     * NUEVO: Crear pedido desde mensaje de contacto
     * POST /admin/pedidos/desde-mensaje/{contactoId}
     */
    public function crearDesdeMensaje(Request $request, $contactoId)
    {
        try {
            $usuarioIdAdmin = Session::get('user_id');

            if (!$usuarioIdAdmin) {
                return response()->json([
                    'success' => false, 
                    'message' => 'Admin no autenticado'
                ], 401);
            }

            // Obtener datos del request
            $comentarios = $request->input('comentarios');
            $personalizacionId = $request->input('personalizacionId');
            $estadoId = (int) $request->input('estadoId', 1);

            // Construir query params
            $query = [
                'estadoId' => $estadoId,
            ];

            // Agregar comentarios si existen
            if ($comentarios !== null && trim($comentarios) !== '') {
                $query['comentarios'] = $comentarios;
            }

            // Agregar personalizacionId solo si es valido
            if (is_numeric($personalizacionId) && (int)$personalizacionId > 0) {
                $query['personalizacionId'] = (int)$personalizacionId;
            }

            // Construir URL con query params
            $endpointConQuery = "/pedidos/desde-contacto/{$contactoId}?" . http_build_query($query);

            // Llamada POST
            $response = $this->apiService->post($endpointConQuery, [], [
                'headers' => [
                    'Authorization' => 'Bearer ' . Session::get('jwt_token')
                ]
            ]);

            if ($response === null || !isset($response['pedId'])) {
                Log::error('PedidoController: Error al crear pedido', [
                    'response' => $response, 
                    'url' => $endpointConQuery
                ]);
                
                return response()->json([
                    'success' => false, 
                    'message' => 'Error al crear el pedido. Verifica que el mensaje exista.'
                ], 500);
            }

            Log::info('PedidoController: Pedido creado desde mensaje', [
                'pedido_id' => $response['pedId'],
                'contacto_id' => $contactoId
            ]);

            return response()->json([
                'success' => true, 
                'message' => 'Pedido creado exitosamente.', 
                'pedido' => $response
            ], 201);

        } catch (\Exception $e) {
            Log::error('PedidoController@crearDesdeMensaje: Excepcion', [
                'contactoId' => $contactoId,
                'error' => $e->getMessage(), 
                'trace' => $e->getTraceAsString()
            ]);
            
            return response()->json([
                'success' => false, 
                'message' => 'Error interno: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Actualizar estado y comentarios de un pedido
     * PUT /admin/pedidos/{id}
     */
    public function update(Request $request, $id)
    {
        try {
            // Validacion
            $validated = $request->validate([
                'estadoId' => 'required|integer|min:1|max:10',
                'comentarios' => 'nullable|string|max:1000'
            ]);

            // Preparar datos
            $data = [
                'estadoId' => (int) $validated['estadoId'],
                'comentarios' => $validated['comentarios'] ?? null
            ];

            // Llamada al API
            $response = $this->apiService->put("/pedidos/{$id}", $data, [
                'headers' => [
                    'Authorization' => 'Bearer ' . Session::get('jwt_token')
                ]
            ]);

            if ($response === null) {
                return back()
                    ->withInput()
                    ->with('error', 'Error al actualizar el pedido.');
            }

            Log::info('PedidoController: Pedido actualizado', ['id' => $id]);

            return redirect()
                ->route('admin.pedidos.index')
                ->with('success', 'Pedido actualizado exitosamente.');

        } catch (\Illuminate\Validation\ValidationException $e) {
            return back()
                ->withErrors($e->errors())
                ->withInput();
        } catch (\Exception $e) {
            Log::error('PedidoController@update: Excepcion', [
                'id' => $id,
                'error' => $e->getMessage()
            ]);

            return back()
                ->withInput()
                ->with('error', 'Error al actualizar el pedido.');
        }
    }

    /**
     * NUEVO: Cambiar estado rapidamente desde el listado
     * PATCH /admin/pedidos/{id}/estado
     */
    public function cambiarEstado(Request $request, $id)
    {
        try {
            $validated = $request->validate([
                'estadoId' => 'required|integer|min:1|max:10'
            ]);

            // Obtener pedido actual
            $pedidoActual = $this->apiService->get("/pedidos/{$id}", [
                'headers' => [
                    'Authorization' => 'Bearer ' . Session::get('jwt_token')
                ]
            ]);

            if ($pedidoActual === null) {
                return response()->json([
                    'success' => false,
                    'message' => 'Pedido no encontrado.'
                ], 404);
            }

            // Actualizar solo el estado
            $data = [
                'estadoId' => (int) $validated['estadoId'],
                'comentarios' => $pedidoActual['pedComentarios'] ?? null
            ];

            $response = $this->apiService->put("/pedidos/{$id}", $data, [
                'headers' => [
                    'Authorization' => 'Bearer ' . Session::get('jwt_token')
                ]
            ]);

            if ($response === null) {
                return response()->json([
                    'success' => false,
                    'message' => 'Error al cambiar el estado del pedido.'
                ], 500);
            }

            Log::info('PedidoController: Estado cambiado', [
                'id' => $id,
                'estadoId' => $validated['estadoId']
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Estado actualizado exitosamente.',
                'estadoId' => $validated['estadoId']
            ]);

        } catch (\Exception $e) {
            Log::error('PedidoController@cambiarEstado: Excepcion', [
                'id' => $id,
                'error' => $e->getMessage()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Error al cambiar el estado.'
            ], 500);
        }
    }

    /**
     * Eliminar pedido
     * DELETE /admin/pedidos/{id}
     */
    public function destroy($id)
    {
        try {
            $response = $this->apiService->delete("/pedidos/{$id}", [
                'headers' => [
                    'Authorization' => 'Bearer ' . Session::get('jwt_token')
                ]
            ]);

            if ($response === null) {
                return response()->json([
                    'success' => false,
                    'message' => 'Error al eliminar el pedido.'
                ], 500);
            }

            Log::info('PedidoController: Pedido eliminado', ['id' => $id]);

            return response()->json([
                'success' => true,
                'message' => 'Pedido eliminado permanentemente.'
            ]);

        } catch (\Exception $e) {
            Log::error('PedidoController@destroy: Excepcion', [
                'id' => $id,
                'error' => $e->getMessage()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Error al eliminar el pedido.'
            ], 500);
        }
    }

    /**
     * Enriquecer pedido con informacion procesada
     */
    private function enriquecerPedido(array $pedido): array
    {
        // Normalizar estructura de estado
        if (!isset($pedido['estado']) && isset($pedido['estadoNombre'])) {
            $pedido['estado'] = [
                'estId' => $pedido['estId'] ?? 1,
                'estNombre' => $pedido['estadoNombre']
            ];
        }

        // Normalizar usuario si viene en formato plano
        if (!isset($pedido['usuario']) && isset($pedido['usuId'])) {
            $pedido['usuario'] = null; // Se cargara por separado si es necesario
        }

        // Normalizar personalizacion
        if (!isset($pedido['personalizacion']) && isset($pedido['perId']) && $pedido['perId'] !== null) {
            $pedido['personalizacion'] = [
                'perId' => $pedido['perId']
            ];
        }

        return $pedido;
    }

    /**
     * Obtener estadisticas de pedidos
     */
    private function getEstadisticas(): array
    {
        try {
            $token = Session::get('jwt_token');
            $headers = ['headers' => ['Authorization' => 'Bearer ' . $token]];

            // Obtener conteo por estados clave
            $responsePendientes = $this->apiService->get('/pedidos/count?estadoId=1', $headers);
            $responseConfirmados = $this->apiService->get('/pedidos/count?estadoId=2', $headers);
            $responseProduccion = $this->apiService->get('/pedidos/count?estadoId=5', $headers);
            $responseEntregados = $this->apiService->get('/pedidos/count?estadoId=9', $headers);

            $pendientes = $responsePendientes['count'] ?? 0;
            $confirmados = $responseConfirmados['count'] ?? 0;
            $produccion = $responseProduccion['count'] ?? 0;
            $entregados = $responseEntregados['count'] ?? 0;

            return [
                'total' => $pendientes + $confirmados + $produccion + $entregados,
                'pendientes' => $pendientes,
                'confirmados' => $confirmados,
                'produccion' => $produccion,
                'entregados' => $entregados
            ];

        } catch (\Exception $e) {
            Log::error('PedidoController@getEstadisticas: Excepcion', [
                'error' => $e->getMessage()
            ]);

            return $this->getEstadisticasVacias();
        }
    }

    /**
     * Obtener lista de estados disponibles
     */
    private function getEstadosDisponibles(): array
    {
        // Lista estatica de estados (del 1 al 10)
        return [
            ['id' => 1, 'nombre' => 'Pendiente Confirmacion'],
            ['id' => 2, 'nombre' => 'Confirmado'],
            ['id' => 3, 'nombre' => 'En Diseno'],
            ['id' => 4, 'nombre' => 'Aprobado por Cliente'],
            ['id' => 5, 'nombre' => 'En Produccion'],
            ['id' => 6, 'nombre' => 'Control de Calidad'],
            ['id' => 7, 'nombre' => 'Listo para Entrega'],
            ['id' => 8, 'nombre' => 'En Camino'],
            ['id' => 9, 'nombre' => 'Entregado'],
            ['id' => 10, 'nombre' => 'Cancelado']
        ];
    }

    /**
     * Estadisticas vacias
     */
    private function getEstadisticasVacias(): array
    {
        return [
            'total' => 0,
            'pendientes' => 0,
            'confirmados' => 0,
            'produccion' => 0,
            'entregados' => 0
        ];
    }
}