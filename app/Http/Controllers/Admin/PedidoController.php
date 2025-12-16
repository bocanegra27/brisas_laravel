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

            // ✅ OPTIMIZACIÓN: Usa Arrow Function para acceder a $this de forma segura.
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
     * Muestra el formulario para crear un nuevo pedido manual.
     * GET /admin/pedidos/crear
     */
    public function create()
    {
        try {
            // Obtener lista de usuarios para poder seleccionar un cliente registrado
            $usuarios = [];
            try {
                $response = $this->apiService->get('/usuarios', [
                    'headers' => ['Authorization' => 'Bearer ' . Session::get('jwt_token')]
                ]);
                
                if (is_array($response)) {
                    // Si la API devuelve paginación, extraemos el contenido, si no, usamos el array directo
                    $data = $response['content'] ?? $response;
                    
                    // ✅ CORRECCIÓN APLICADA: Uso de Arrow Function (fn) para capturar $this automáticamente.
                    $usuarios = array_map(fn($user) => $this->normalizarUsuarioParaSelect($user), $data);
                }
            } catch (\Exception $e) {
                Log::warning('No se pudieron cargar usuarios para el select de crear pedido.');
            }

            return view('admin.pedidos.create', [
                'usuarios' => $usuarios
            ]);
        } catch (\Exception $e) {
            return redirect()->route('admin.pedidos.index')->with('error', 'Error al cargar formulario de creación.');
        }
    }

    /**
     * Guarda el nuevo pedido en la base de datos.
     * POST /admin/pedidos
     */
    public function store(Request $request)
    {
        try {
            // 1. Validar datos
            $request->validate([
                'tipo_cliente' => 'required|in:registrado,externo',
                'usuIdCliente' => 'required_if:tipo_cliente,registrado',
                'nombre_cliente_ext' => 'required_if:tipo_cliente,externo',
                'telefono_cliente_ext' => 'required_if:tipo_cliente,externo',
                'descripcion' => 'required|string|max:1000'
            ]);

            // 2. Preparar datos (Array plano para multipart)
            $data = [
                'pedComentarios' => $request->input('descripcion'),
                'estId' => '1', // Enviar como string
            ];

            if ($request->input('tipo_cliente') === 'registrado') {
                $data['usuIdCliente'] = (string) $request->input('usuIdCliente');
            } else {
                $nombre = trim($request->input('nombre_cliente_ext'));
                $telefono = trim($request->input('telefono_cliente_ext'));
                // El formato guardado en Java Pedido.pedIdentificadorCliente
                $data['pedIdentificadorCliente'] = "{$nombre} - {$telefono}"; 
            }

            // 3. Obtener Configuración
            $token = Session::get('jwt_token');
            // Usamos la misma config que usa tu ApiService para mantener coherencia
            $baseUrl = config('services.spring_api.url', 'http://localhost:8080/api');

            // 4. Usar Http::asMultipart() para enviar el payload correctamente
            $response = Http::withToken($token)
                ->asMultipart() 
                ->post("{$baseUrl}/pedidos", $data);

            // 5. Verificar Respuesta
            if ($response->successful()) {
                return redirect()->route('admin.pedidos.index')->with('success', 'Pedido creado exitosamente.');
            }

            // Loguear error si falla
            Log::error('API Error crear pedido: ' . $response->body());
            return back()->withInput()->with('error', 'Error al guardar. La API respondió: ' . $response->status());

        } catch (\Exception $e) {
            Log::error('Error creando pedido admin: ' . $e->getMessage());
            return back()->withInput()->with('error', 'Error interno al crear el pedido.');
        }
    }

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

            // 3. Obtener el HISTORIAL directamente aquí
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
                'historial' => $historial 
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

    /**
     * ❌ MÉTODO ELIMINADO: update() 
     * Se eliminó porque la lógica de cambio de estado fue unificada en actualizarEstadoConHistorial.
     */
    
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
        // Asegura que el estado esté bien mapeado
        if (!isset($pedido['estado']) && isset($pedido['estadoNombre'])) {
            $pedido['estado'] = ['estId' => $pedido['estId'] ?? 1, 'estNombre' => $pedido['estadoNombre']];
        }
        
        $token = Session::get('jwt_token');
        $clienteInfo = null;
        
        // ✅ CORRECCIÓN FINAL: Usa nombreCliente, no nombreEmpleado.
        $nombreClienteDisplay = $pedido['nombreCliente'] ?? null; 

        // Si no tenemos un nombre, PERO tenemos un ID de cliente, hacemos la búsqueda detallada.
        if (empty($nombreClienteDisplay) && !empty($pedido['usuIdCliente']) && $token) {
            try {
                $clienteInfo = $this->apiService->get("/usuarios/{$pedido['usuIdCliente']}", ['headers' => ['Authorization' => 'Bearer ' . $token]]);
                if (is_array($clienteInfo)) {
                    $clienteInfo = $this->normalizarClienteKeys($clienteInfo, 'usuario');
                    $nombreClienteDisplay = $clienteInfo['usuNombre'] ?? $nombreClienteDisplay;
                    $clienteInfo['tipo'] = 'usuario_registrado';
                }
            } catch (\Exception $e) {
                    // Si falla, el nombre sigue siendo el que vino de Java (si vino) o null.
            }
        
        // 2. Cliente Manual / Externo (Si no fue enriquecido por Java, lo hacemos ahora)
        } elseif (empty($nombreClienteDisplay) && !empty($pedido['pedIdentificadorCliente'])) {
            $nombreClienteDisplay = $pedido['pedIdentificadorCliente'];
            $clienteInfo = ['tipo' => 'manual', 'nombre' => $nombreClienteDisplay];
        
        // 3. Cliente desde Contacto (Si no fue enriquecido por Java, lo hacemos ahora)
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

        // Asignamos el nombre final: Confía en lo que viene de Java ($pedido['nombreCliente']) si no se modificó antes.
        $pedido['nombreCliente'] = $nombreClienteDisplay ?? $pedido['nombreCliente'] ?? 'Desconocido/Anónimo'; 
        
        // Aseguramos que el detalle esté presente si es un pedido manual.
        if (!isset($pedido['clienteDetalles']) && !empty($pedido['pedIdentificadorCliente'])) {
             $pedido['clienteDetalles'] = ['tipo' => 'manual', 'nombre' => $pedido['pedIdentificadorCliente']];
        } else {
             $pedido['clienteDetalles'] = $clienteInfo ?? ['tipo' => 'sin_detalles', 'nombre' => $pedido['nombreCliente'] ?? 'Anónimo'];
        }

        return $pedido;
    }
    
    /**
     * Normaliza las claves de un array de datos de cliente/contacto.
     */
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
            // Asume que los datos de contacto pueden tener claves ligeramente diferentes
            return [
                'conId' => $data['id'] ?? $data['con_id'] ?? null,
                'conNombre' => $data['nombre'] ?? $data['con_nombre'] ?? null,
                'conCorreo' => $data['correo'] ?? $data['con_correo'] ?? null,
                'conTelefono' => $data['telefono'] ?? $data['con_telefono'] ?? null,
            ];
        }
        return $data;
    }

    /**
     * Normaliza las claves de un objeto de usuario para el select de la vista de creación.
     */
    private function normalizarUsuarioParaSelect(array $user): array
    {
        // Asumimos que los campos pueden llamarse 'id'/'nombre'/'correo' o 'usuId'/'usuNombre'/'usuCorreo'
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