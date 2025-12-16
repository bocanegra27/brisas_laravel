<?php

namespace App\Http\Controllers\Designer;

use App\Http\Controllers\Controller;
use App\Services\ApiService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Http; 

class DesignerPedidoController extends Controller
{
    private ApiService $apiService;

    public function __construct(ApiService $apiService)
    {
        $this->apiService = $apiService;
    }

    /**
     * Mostrar listado de pedidos CON FILTRO POR DISEÑADOR ASIGNADO
     */
    public function index(Request $request)
    {
        try {
            $page = $request->get('page', 0);
            $size = $request->get('size', 10);
            $estadoId = $request->get('estadoId');
            $codigo = $request->get('codigo');
            
            $usuIdEmpleado = Session::get('user_id'); 
            
            if (!$usuIdEmpleado) {
                return view('designer.pedidos.index')->with('error', 'No se pudo identificar al diseñador.');
            }

            $params = [
                'page' => $page, 
                'size' => $size,
                'usuIdEmpleado' => $usuIdEmpleado
            ];

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

            $stats = $this->getEstadisticas($usuIdEmpleado); 
            $estados = $this->getEstadosDisponibles();

            if ($response === null) {
                 // ... (Manejo de error) ...
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

            $pedidos = array_map(fn($pedido) => $this->enriquecerPedido($pedido), $pedidos);
            
            $estadoMapeo = $this->getEstadoMapeo();

            $data = [
                'pedidos' => $pedidos,
                'totalElements' => $totalElements,
                'totalPages' => $totalPages,
                'currentPage' => $currentPage,
                'pageSize' => $pageSize,
                'stats' => $stats,
                'estados' => $estados,
                'estadoMapeo' => $estadoMapeo,
                'filtros' => [
                    'estadoId' => $estadoId,
                    'codigo' => $codigo
                ]
            ];
            
            return view('designer.pedidos.index', $data);

        } catch (\Exception $e) {
            Log::error('DesignerPedidoController@index: Excepcion', ['error' => $e->getMessage()]);
            return view('designer.pedidos.index')->with([
                'pedidos' => [],
                'totalElements' => 0,
                'totalPages' => 0,
                'currentPage' => 0,
                'pageSize' => 10,
                'stats' => $this->getEstadisticasVacias(),
                'estados' => $this->getEstadosDisponibles(),
                'filtros' => []
            ])->with('error', 'Error al cargar los pedidos asignados.');
        }
    }

    public function create()
    {
        return redirect()->route('designer.pedidos.index')->with('warning', 'Función de creación no disponible para diseñadores.');
    }

    public function gestionar($id)
    {
         try {
            $responsePedido = $this->apiService->get("/pedidos/{$id}", [
                'headers' => ['Authorization' => 'Bearer ' . Session::get('jwt_token')]
            ]);

            if (!is_array($responsePedido) || !isset($responsePedido['pedId'])) {
                return redirect()->route('designer.pedidos.index')->with('error', 'Pedido no encontrado.');
            }
            
            $usuIdEmpleado = Session::get('user_id'); 
            if (($responsePedido['usuIdEmpleado'] ?? null) != $usuIdEmpleado) {
                 return redirect()->route('designer.pedidos.index')->with('error', 'Acceso denegado: Este pedido no te ha sido asignado.');
            }

            $pedido = $this->enriquecerPedido($responsePedido);
            
            $estadoId = $pedido['estado']['estId'] ?? $pedido['estId'] ?? 1;

            $estadosArray = $this->getEstadosDisponibles();
            $estados = [];
            foreach ($estadosArray as $estado) {
                $estados[$estado['id']] = $estado['nombre']; 
            }

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

            return view('designer.pedidos.gestionar', [
                'pedido' => $pedido,
                'estados' => $estados,
                'estadoId' => $estadoId,
                'historial' => $historial 
            ]);

        } catch (\Exception $e) {
            Log::error('DesignerPedidoController@gestionar: Excepcion', ['id' => $id, 'error' => $e->getMessage()]);
            return redirect()->route('designer.pedidos.index')->with('error', 'Error al cargar el pedido.');
        }
    }

    /**
     * Actualizar estado con historial e IMAGEN (Multipart)
     */
    public function actualizarEstadoConHistorial(Request $request, $pedidoId)
    {
        // 🛡️ CORRECCIÓN 2: Añadir Validación de Entrada 
        $request->validate([
            'estadoId' => 'required|integer|min:1|max:10', 
        ]);
        
        try {
            $nuevoEstadoId = (int) $request->input('estadoId');
            $comentarios = $request->input('comentarios') ?? 'Actualización de estado por diseñador.';
            $imagen = $request->file('imagen');
            $token = Session::get('jwt_token');

            // ✅ CORRECCIÓN 1: Usar la clave de configuración correcta (spring_api.url).
            $baseUrl = config('services.spring_api.url') ?? 'http://localhost:8080/api'; 
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

            Log::error('DesignerPedidoController@actualizarEstado: API Fallo.', ['status' => $response->status(), 'body' => $response->body()]);
            return response()->json([
                'success' => false,
                'message' => 'Error API: ' . $response->body()
            ], $response->status());

        } catch (\Exception $e) {
            Log::error('DesignerPedidoController@actualizarEstado: Excepción.', ['error' => $e->getMessage()]);
            return response()->json([
                'success' => false,
                'message' => 'Error interno: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * MÉTODOS AUXILIARES COPIADOS DEL ADMIN
     */
     
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
    
    private function getEstadisticas(?int $usuIdEmpleado = null): array
    {
        try {
            $token = Session::get('jwt_token');
            $headers = ['headers' => ['Authorization' => 'Bearer ' . $token]];
            
            $baseQuery = $usuIdEmpleado ? "?usuIdEmpleado={$usuIdEmpleado}&estadoId=" : "?estadoId=";
            
            $pendientes = 0; $confirmados = 0; $produccion = 0; $entregados = 0;
            try { $pendientes = $this->apiService->get('/pedidos/count' . $baseQuery . '1', $headers)['count'] ?? 0; } catch (\Exception $e) {}
            try { $confirmados = $this->apiService->get('/pedidos/count' . $baseQuery . '2', $headers)['count'] ?? 0; } catch (\Exception $e) {}
            try { $produccion = $this->apiService->get('/pedidos/count' . $baseQuery . '5', $headers)['count'] ?? 0; } catch (\Exception $e) {}
            try { $entregados = $this->apiService->get('/pedidos/count' . $baseQuery . '9', $headers)['count'] ?? 0; } catch (\Exception $e) {}

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
        $nombreClienteDisplay = $pedido['nombreCliente'] ?? null; 

        if (empty($nombreClienteDisplay) && !empty($pedido['usuIdCliente']) && $token) {
             try {
                $clienteInfo = $this->apiService->get("/usuarios/{$pedido['usuIdCliente']}", ['headers' => ['Authorization' => 'Bearer ' . $token]]);
                if (is_array($clienteInfo)) {
                    $clienteInfo = $this->normalizarClienteKeys($clienteInfo, 'usuario');
                    $nombreClienteDisplay = $clienteInfo['usuNombre'] ?? $nombreClienteDisplay;
                    $clienteInfo['tipo'] = 'usuario_registrado';
                }
            } catch (\Exception $e) {}
        } elseif (empty($nombreClienteDisplay) && !empty($pedido['pedIdentificadorCliente'])) {
            $nombreClienteDisplay = $pedido['pedIdentificadorCliente'];
            $clienteInfo = ['tipo' => 'manual', 'nombre' => $nombreClienteDisplay];
        } elseif (empty($nombreClienteDisplay) && !empty($pedido['conId']) && $token) {
            try {
                $clienteInfo = $this->apiService->get("/contactos/{$pedido['conId']}", ['headers' => ['Authorization' => 'Bearer ' . $token]]);
                if (is_array($clienteInfo)) {
                    $clienteInfo = $this->normalizarClienteKeys($clienteInfo, 'contacto');
                    $nombreClienteDisplay = $clienteInfo['conNombre'] ?? $nombreClienteDisplay;
                    $clienteInfo['tipo'] = 'contacto_externo';
                }
            } catch (\Exception $e) {}
        }

        $pedido['nombreCliente'] = $nombreClienteDisplay ?? $pedido['nombreCliente'] ?? 'Desconocido/Anónimo'; 
        
        if (!isset($pedido['clienteDetalles']) && !empty($pedido['pedIdentificadorCliente'])) {
             $pedido['clienteDetalles'] = ['tipo' => 'manual', 'nombre' => $pedido['pedIdentificadorCliente']];
        } else {
             $pedido['clienteDetalles'] = $clienteInfo ?? ['tipo' => 'sin_detalles', 'nombre' => $pedido['nombreCliente'] ?? 'Anónimo'];
        }

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
    
    private function normalizarUsuarioParaSelect(array $user): array
    {
        $id = $user['usuId'] ?? $user['id'] ?? null;
        $nombre = $user['usuNombre'] ?? $user['nombre'] ?? null;
        $correo = $user['usuCorreo'] ?? $user['correo'] ?? null;
        $rol = $user['rol']['rolNombre'] ?? null;

        return [
            'usuId' => $id,
            'usuNombre' => $nombre,
            'usuCorreo' => $correo,
            'rol' => ['rolNombre' => $rol] 
        ];
    }
}