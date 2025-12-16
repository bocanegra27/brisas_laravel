<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Services\ApiService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Http; 

class PedidoController extends Controller
{
    private ApiService $apiService;

    public function __construct(ApiService $apiService)
    {
        $this->apiService = $apiService;
    }

    /**
     * Mostrar listado de pedidos con paginacion y filtros
     */
    public function index(Request $request)
    {
        try {
            $page = $request->get('page', 0);
            $size = $request->get('size', 10);
            $estadoId = $request->get('estadoId');
            $codigo = $request->get('codigo');

            $params = ['page' => $page, 'size' => $size];

            if ($estadoId !== null && $estadoId !== '') {
                $params['estadoId'] = $estadoId;
            }
            if ($codigo !== null && $codigo !== '') {
                $params['codigo'] = $codigo;
            }

            $queryString = http_build_query($params);
            $endpoint = '/pedidos?' . $queryString;

            $response = $this->apiService->get($endpoint, [
                'headers' => ['Authorization' => 'Bearer ' . Session::get('jwt_token')]
            ]);

            $stats = $this->getEstadisticas();
            $estados = $this->getEstadosDisponibles();
            $disenadores = [];
            
            try {
                $disenadoresResponse = $this->apiService->get('/usuarios/empleados', [
                    'headers' => ['Authorization' => 'Bearer ' . Session::get('jwt_token')]
                ]);
                if (is_array($disenadoresResponse)) {
                    $disenadores = $disenadoresResponse;
                }
            } catch (\Exception $e) {
                Log::warning('No se pudieron cargar diseñadores: ' . $e->getMessage());
            }

            if ($response === null) {
                return view('admin.pedidos.index')->with([
                     'pedidos' => [],
                     'totalElements' => 0,
                     'totalPages' => 0,
                     'currentPage' => 0,
                     'pageSize' => $size,
                     'stats' => $stats,
                     'estados' => $estados,
                     'disenadores' => $disenadores,
                     'filtros' => []
                 ])->with('error', 'Error al cargar los pedidos.');
            }

            $pedidos = [];
            $totalElements = 0;
            $totalPages = 0;
            $currentPage = 0;
            $pageSize = $size;

            if (isset($response['content']) && is_array($response['content'])) {
                $pedidos = $response['content'];
                $totalElements = $response['totalElements'] ?? count($pedidos);
                $totalPages = $response['totalPages'] ?? 1;
                $currentPage = $response['pageable']['pageNumber'] ?? 0;
                $pageSize = $response['pageable']['pageSize'] ?? $size;
            } elseif (is_array($response)) {
                $pedidos = $response;
                $totalElements = count($pedidos);
                $totalPages = (int) ceil($totalElements / $size);
                $currentPage = $page;
                $pageSize = $size;
            }

            $pedidos = array_map(function($pedido) {
                return $this->enriquecerPedido($pedido);
            }, $pedidos);
            
            $estadoMapeo = $this->getEstadoMapeo();

            $data = [
                'pedidos' => $pedidos,
                'totalElements' => $totalElements,
                'totalPages' => $totalPages,
                'currentPage' => $currentPage,
                'pageSize' => $pageSize,
                'stats' => $stats,
                'estados' => $estados,
                'disenadores' => $disenadores,
                'estadoMapeo' => $estadoMapeo,
                'filtros' => [
                    'estadoId' => $estadoId,
                    'codigo' => $codigo
                ]
            ];

            return view('admin.pedidos.index', $data);

        } catch (\Exception $e) {
            Log::error('PedidoController@index: Excepcion', ['error' => $e->getMessage()]);
            return view('admin.pedidos.index')->with([
                'pedidos' => [],
                'totalElements' => 0,
                'totalPages' => 0,
                'currentPage' => 0,
                'pageSize' => 10,
                'stats' => $this->getEstadisticasVacias(),
                'estados' => $this->getEstadosDisponibles(),
                'disenadores' => [],
                'filtros' => []
            ])->with('error', 'Error al cargar los pedidos.');
        }
    }

    /**
     * Vista de gestion del pedido con Timeline.
     * GET /admin/pedidos/{id}/gestionar
     */
    /**
     * Vista de gestion del pedido con Timeline.
     * GET /admin/pedidos/{id}/gestionar
     */
   /**
     * Vista de gestión de pedido (Versión Clásica + Historial Directo)
     * GET /admin/pedidos/{id}/gestionar
     */
    public function gestionar($id)
    {
        try {
            // 1. Obtener los datos del pedido
            $responsePedido = $this->apiService->get("/pedidos/{$id}", [
                'headers' => ['Authorization' => 'Bearer ' . Session::get('jwt_token')]
            ]);

            if (!is_array($responsePedido) || !isset($responsePedido['pedId'])) {
                return redirect()->route('admin.pedidos.index')->with('error', 'Pedido no encontrado.');
            }

            $pedido = $this->enriquecerPedido($responsePedido);
            
            // Calcular estado actual
            $estadoId = $pedido['estado']['estId'] ?? $pedido['estId'] ?? 1;

            // 2. Obtener la lista de estados para el formulario
            $estadosArray = $this->getEstadosDisponibles();
            $estados = [];
            foreach ($estadosArray as $estado) {
                $estados[$estado['id']] = $estado['nombre']; 
            }

            // 3. 🔥 NUEVO: Obtener el HISTORIAL directamente aquí
            $historial = [];
            try {
                $responseHistorial = $this->apiService->get("/pedidos/{$id}/historial", [
                    'headers' => ['Authorization' => 'Bearer ' . Session::get('jwt_token')]
                ]);
                if (is_array($responseHistorial)) {
                    $historial = $responseHistorial;
                }
            } catch (\Exception $e) {
                Log::error("Error cargando historial para pedido $id: " . $e->getMessage());
            }

            // 4. Retornar vista con TODO listo
            return view('admin.pedidos.gestionar', [
                'pedido' => $pedido,
                'estados' => $estados,
                'estadoId' => $estadoId,
                'historial' => $historial // <--- Pasamos el historial directo a la vista
            ]);

        } catch (\Exception $e) {
            Log::error('PedidoController@gestionar: Excepcion', ['id' => $id, 'error' => $e->getMessage()]);
            return redirect()->route('admin.pedidos.index')->with('error', 'Error al cargar el pedido.');
        }
    }

    /**
     * Actualizar estado con historial e IMAGEN (Multipart)
     */
    public function actualizarEstadoConHistorial(Request $request, $pedidoId)
    {
        try {
            $nuevoEstadoId = (int) $request->input('estadoId');
            $comentarios = $request->input('comentarios') ?? 'Actualización de estado.';
            $imagen = $request->file('imagen');
            $token = Session::get('jwt_token');

            $baseUrl = config('services.api.url') ?? 'http://localhost:8080/api'; 
            $httpRequest = Http::withToken($token);

            if ($imagen) {
                $httpRequest->attach(
                    'imagen', 
                    file_get_contents($imagen->getRealPath()), 
                    $imagen->getClientOriginalName()
                );
            }

            $response = $httpRequest->patch("{$baseUrl}/pedidos/{$pedidoId}/estado", [
                'nuevoEstadoId' => $nuevoEstadoId,
                'comentarios' => $comentarios
            ]);

            if ($response->successful()) {
                return response()->json([
                    'success' => true,
                    'message' => 'Estado actualizado y evidencia guardada.',
                    'pedido' => $response->json()
                ]);
            }

            Log::error('PedidoController@actualizarEstadoConHistorial: API Fallo.', ['status' => $response->status(), 'body' => $response->body()]);
            return response()->json([
                'success' => false,
                'message' => 'Error API: ' . $response->body()
            ], $response->status());

        } catch (\Exception $e) {
            Log::error('PedidoController@actualizarEstadoConHistorial: Excepción.', ['error' => $e->getMessage()]);
            return response()->json([
                'success' => false,
                'message' => 'Error interno: ' . $e->getMessage()
            ], 500);
        }
    }

    public function obtenerHistorial($pedidoId)
    {
        try {
            $response = $this->apiService->get("/pedidos/{$pedidoId}/historial", [
                'headers' => ['Authorization' => 'Bearer ' . Session::get('jwt_token')]
            ]);

            if ($response === null || !is_array($response)) {
                return response()->json(['success' => false, 'message' => 'Error al obtener el historial.'], 500);
            }
            
            return response()->json(['success' => true, 'historial' => $response]);

        } catch (\Exception $e) {
            Log::error('PedidoController@obtenerHistorial: Excepción.', ['pedidoId' => $pedidoId, 'error' => $e->getMessage()]);
            return response()->json(['success' => false, 'message' => 'Error interno al cargar el historial.'], 500);
        }
    }

    public function crearDesdeMensaje(Request $request, $contactoId)
    {
        try {
            $comentarios = $request->input('comentarios');
            $personalizacionId = $request->input('personalizacionId');
            $estadoId = (int) $request->input('estadoId', 1);

            $query = ['estadoId' => $estadoId];
            if ($comentarios) $query['comentarios'] = $comentarios;
            if (is_numeric($personalizacionId) && (int)$personalizacionId > 0) $query['personalizacionId'] = (int)$personalizacionId;

            $endpointConQuery = "/pedidos/desde-contacto/{$contactoId}?" . http_build_query($query);
            $response = $this->apiService->post($endpointConQuery, [], [
                'headers' => ['Authorization' => 'Bearer ' . Session::get('jwt_token')]
            ]);

            if ($response === null || !isset($response['pedId'])) {
                return response()->json(['success' => false, 'message' => 'Error al crear el pedido.'], 500);
            }

            return response()->json(['success' => true, 'message' => 'Pedido creado exitosamente.', 'pedido' => $response], 201);

        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => 'Error interno: ' . $e->getMessage()], 500);
        }
    }

    public function update(Request $request, $id)
    {
        try {
            $validated = $request->validate([
                'estadoId' => 'required|integer|min:1|max:10',
                'comentarios' => 'nullable|string|max:1000'
            ]);

            $data = [
                'estadoId' => (int) $validated['estadoId'],
                'comentarios' => $validated['comentarios'] ?? null
            ];

            $response = $this->apiService->put("/pedidos/{$id}", $data, [
                'headers' => ['Authorization' => 'Bearer ' . Session::get('jwt_token')]
            ]);

            if ($response === null) {
                return back()->withInput()->with('error', 'Error al actualizar el pedido.');
            }

            return redirect()->route('admin.pedidos.index')->with('success', 'Pedido actualizado exitosamente.');

        } catch (\Exception $e) {
            return back()->withInput()->with('error', 'Error al actualizar el pedido.');
        }
    }

    public function destroy($id)
    {
        try {
            $response = $this->apiService->delete("/pedidos/{$id}", [
                'headers' => ['Authorization' => 'Bearer ' . Session::get('jwt_token')]
            ]);

            if ($response === null) {
                return response()->json(['success' => false, 'message' => 'Error al eliminar el pedido.'], 500);
            }

            return response()->json(['success' => true, 'message' => 'Pedido eliminado permanentemente.']);

        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => 'Error al eliminar el pedido.'], 500);
        }
    }
    
    public function asignarEmpleado(Request $request, $pedidoId)
    {
        $request->validate(['usuIdEmpleado' => 'required|integer']);
        try {
            $data = ['usuIdEmpleado' => $request->input('usuIdEmpleado')];
            $response = $this->apiService->patch("/pedidos/{$pedidoId}/asignar", $data, [
                'headers' => ['Authorization' => 'Bearer ' . Session::get('jwt_token')]
            ]);

            if ($response === null) throw new \Exception('API Error');
            return response()->json(['success' => true, 'message' => 'Diseñador asignado con éxito.', 'pedido' => $response]);

        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => 'Error en el servidor.'], 500);
        }
    }

    // ===============================================
    // MÉTODOS PRIVADOS / AUXILIARES
    // ===============================================

    private function getEstadosDisponibles(): array
    {
        return [
            ['id' => 1, 'nombre' => '1. Cotización Pendiente'],
            ['id' => 2, 'nombre' => '2. Pago Diseño Pendiente'],
            ['id' => 3, 'nombre' => '3. Diseño en Proceso'],
            ['id' => 4, 'nombre' => '4. Diseño Aprobado'],
            ['id' => 5, 'nombre' => '5. Tallado (Producción)'],
            ['id' => 6, 'nombre' => '6. Engaste'],
            ['id' => 7, 'nombre' => '7. Pulido'],
            ['id' => 8, 'nombre' => '8. Inspección de Calidad'],
            ['id' => 9, 'nombre' => '9. Finalizado (Listo para Entrega)'],
            ['id' => 10, 'nombre' => '10. Cancelado']
        ];
    }
    
    private function getEstadisticas(): array
    {
        try {
            $token = Session::get('jwt_token');
            $headers = ['headers' => ['Authorization' => 'Bearer ' . $token]];
            
            $pendientes = 0; $confirmados = 0; $produccion = 0; $entregados = 0;
            try { $pendientes = $this->apiService->get('/pedidos/count?estadoId=1', $headers)['count'] ?? 0; } catch (\Exception $e) {}
            try { $confirmados = $this->apiService->get('/pedidos/count?estadoId=2', $headers)['count'] ?? 0; } catch (\Exception $e) {}
            try { $produccion = $this->apiService->get('/pedidos/count?estadoId=5', $headers)['count'] ?? 0; } catch (\Exception $e) {}
            try { $entregados = $this->apiService->get('/pedidos/count?estadoId=9', $headers)['count'] ?? 0; } catch (\Exception $e) {}

            return [
                'total' => $pendientes + $confirmados + $produccion + $entregados,
                'pendientes' => $pendientes,
                'confirmados' => $confirmados,
                'produccion' => $produccion,
                'entregados' => $entregados
            ];
        } catch (\Exception $e) {
            return $this->getEstadisticasVacias();
        }
    }
    
    private function getEstadisticasVacias(): array
    {
        return ['total' => 0, 'pendientes' => 0, 'confirmados' => 0, 'produccion' => 0, 'entregados' => 0];
    }

    private function getEstadoMapeo()
    {
        return [
            'cotizacion_pendiente' => 'Cotización Pendiente',
            'pago_diseno_pendiente' => 'Pago Diseño Pendiente',
            'diseno_en_proceso' => 'Diseño en Proceso',
            'diseno_aprobado' => 'Diseño Aprobado',
            'tallado_produccion' => 'Tallado (Producción)',
            'engaste' => 'Engaste',
            'pulido' => 'Pulido',
            'inspeccion_calidad' => 'Inspección de Calidad',
            'finalizado_listo_entrega' => 'Finalizado',
            'cancelado' => 'Cancelado',
            'desconocido' => 'Estado Desconocido'
        ];
    }

    private function enriquecerPedido(array $pedido): array
    {
        if (!isset($pedido['estado']) && isset($pedido['estadoNombre'])) {
             $pedido['estado'] = ['estId' => $pedido['estId'] ?? 1, 'estNombre' => $pedido['estadoNombre']];
        }
        
        $token = Session::get('jwt_token');
        $clienteInfo = null;

        if (!empty($pedido['usuIdCliente']) && $token) {
            try {
                $clienteInfo = $this->apiService->get("/usuarios/{$pedido['usuIdCliente']}", ['headers' => ['Authorization' => 'Bearer ' . $token]]);
                if (is_array($clienteInfo)) {
                    $clienteInfo = $this->normalizarClienteKeys($clienteInfo, 'usuario');
                    $clienteInfo['tipo'] = 'usuario_registrado';
                }
            } catch (\Exception $e) {}
        } elseif (!empty($pedido['conId']) && $token) {
            try {
                $clienteInfo = $this->apiService->get("/contactos/{$pedido['conId']}", ['headers' => ['Authorization' => 'Bearer ' . $token]]);
                if (is_array($clienteInfo)) {
                    $clienteInfo = $this->normalizarClienteKeys($clienteInfo, 'contacto');
                    $clienteInfo['tipo'] = 'contacto_externo';
                }
            } catch (\Exception $e) {}
        } else {
            if (!empty($pedido['nombreCliente'])) {
                $clienteInfo = ['tipo' => 'sin_detalles', 'nombre' => $pedido['nombreCliente']];
            }
        }
        
        $pedido['clienteDetalles'] = $clienteInfo;
        return $pedido;
    }

    private function normalizarClienteKeys(array $data, string $tipo): array
    {
        if ($tipo === 'usuario') {
            return [
                'usuId' => $data['id'] ?? $data['usu_id'] ?? null,
                'usuNombre' => $data['nombre'] ?? $data['usu_nombre'] ?? null,
                'usuCorreo' => $data['correo'] ?? $data['usu_correo'] ?? null,
                'usuTelefono' => $data['telefono'] ?? $data['usu_telefono'] ?? null,
            ];
        } elseif ($tipo === 'contacto') {
            return [
                'conId' => $data['id'] ?? $data['con_id'] ?? null,
                'conNombre' => $data['nombre'] ?? $data['con_nombre'] ?? null,
                'conCorreo' => $data['correo'] ?? $data['con_correo'] ?? null,
                'conTelefono' => $data['telefono'] ?? $data['con_telefono'] ?? null,
            ];
        }
        return $data;
    }
}