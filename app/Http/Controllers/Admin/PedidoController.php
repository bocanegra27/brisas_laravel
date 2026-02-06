<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Services\ApiService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Http;

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

        // Llamada al API con autenticacion (Paso 1: Obtener Pedidos)
        $response = $this->apiService->get($endpoint, [
            'headers' => [
                'Authorization' => 'Bearer ' . Session::get('jwt_token')
            ]
        ]);

        // Verificar respuesta (Lógica de manejo de errores si la API falla)
        if ($response === null) {
            Log::error('PedidoController: Error al obtener pedidos del API');
            // ... (Devuelve la vista con arrays vacíos si hay error) ...
            return view('admin.pedidos.index')->with([
                 'pedidos' => [],
                 'totalElements' => 0,
                 'totalPages' => 0,
                 'currentPage' => 0,
                 'pageSize' => $size,
                 'stats' => $this->getEstadisticasVacias(),
                 'estados' => $this->getEstadosDisponibles()
             ])->with('error', 'Error al cargar los pedidos.');
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
            // Array simple de pedidos (Si no hay paginación)
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
        
        // obtener lista de diseñadores para filtro
        $disenadoresResponse = $this->apiService->get('/usuarios/empleados', [
            'headers' => [
                'Authorization' => 'Bearer ' . Session::get('jwt_token')
            ]
        ]);
        
        $disenadores = is_array($disenadoresResponse) ? $disenadoresResponse : [];
        // ----------------------------------------------------
        
        // Obtener estadisticas
        $stats = $this->getEstadisticas();
        
        // Obtener lista de estados disponibles
        $estados = $this->getEstadosDisponibles();

        $estadoMapeo = $this->getEstadoMapeo();

        // Preparar datos para la vista
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
 * Vista de gestion del pedido con Timeline.
 * GET /admin/pedidos/{id}/gestionar
 */
public function gestionar($id)
{
    try {
        $response = $this->apiService->get("/pedidos/{$id}", [
            'headers' => [
                'Authorization' => 'Bearer ' . Session::get('jwt_token')
            ]
        ]);

        // Validación simple
        if (!is_array($response) || !isset($response['pedId'])) {
            return redirect()
                ->route('admin.pedidos.index')
                ->with('error', 'Pedido no encontrado.');
        }

        // Enriquecer el pedido (ahora sí funcionará correctamente)
        $pedido = $this->enriquecerPedido($response);

        $estadosArray = $this->getEstadosDisponibles();
        $estados = [];
        foreach ($estadosArray as $estado) {
            $estados[$estado['id']] = $estado['nombre']; 
        }

        return view('admin.pedidos.gestionar', [
            'pedido' => $pedido,
            'estados' => $estados,
        ]);

    } catch (\Exception $e) {
        Log::error('PedidoController@gestionar: Excepcion', [
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
     * Llama al backend para actualizar el estado y registrar el evento en el historial.
     * PATCH /admin/pedidos/{id}/estado-historial
     */
    public function actualizarEstadoConHistorial(Request $request, $id)
    {
        // 1. Validar (La imagen es opcional)
        $request->validate([
            'estadoId' => 'required|integer',
            'comentarios' => 'nullable|string',
            'his_imagen' => 'nullable|image|max:5120', // Máximo 5MB
        ]);

        $data = [
            'nuevoEstadoId' => $request->estadoId,
            'comentarios' => $request->comentarios ?? 'Sin comentarios.',
        ];

        // 2. Enviar a Spring Boot
        if ($request->hasFile('his_imagen')) {
            // Si hay archivo, usamos attachFile
            $response = $this->apiService->attachFile(
                "/pedidos/{$id}/estado-con-foto", 
                $data, 
                $request->file('his_imagen'), 
                'his_imagen', // Nombre del @RequestParam en Java
                ['headers' => ['Authorization' => 'Bearer ' . session('jwt_token')]]
            );
        } else {
            // Si no hay archivo, usamos un POST normal al endpoint de historial que ya tienes
            $response = $this->apiService->patch("/pedidos/{$id}/estado", [
                'nuevoEstadoId' => (int)$request->estadoId,
                'comentarios' => $request->comentarios
            ], ['headers' => ['Authorization' => 'Bearer ' . session('jwt_token')]]);
        }

        if ($response) {
            return response()->json(['success' => true, 'message' => 'Estado actualizado correctamente.']);
        }

        return response()->json(['success' => false, 'message' => 'Error al comunicar con el servidor de joyería.'], 500);
    }

    /**
     * Obtiene el historial de un pedido para la vista de gestión.
     * GET /admin/pedidos/{id}/historial
     */
    public function obtenerHistorial($pedidoId)
    {
        try {
            // Llamada al endpoint de Spring Boot para obtener el historial
            $response = $this->apiService->get("/pedidos/{$pedidoId}/historial", [
                'headers' => ['Authorization' => 'Bearer ' . Session::get('jwt_token')]
            ]);

            if ($response === null || !is_array($response)) {
                Log::error('PedidoController@obtenerHistorial: Error al obtener historial', [
                    'pedidoId' => $pedidoId,
                    'response' => $response
                ]);
                return response()->json([
                    'success' => false, 
                    'message' => 'Error al obtener el historial.'
                ], 500);
            }
            
            // Spring Boot devuelve una lista ordenada de DTOs del historial
            return response()->json([
                'success' => true, 
                'historial' => $response
            ]);

        } catch (\Exception $e) {
            Log::error('PedidoController@obtenerHistorial: Excepción.', [
                'pedidoId' => $pedidoId,
                'error' => $e->getMessage()
            ]);
            return response()->json([
                'success' => false, 
                'message' => 'Error interno al cargar el historial.'
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
private function enriquecerPedido(array $pedido): array
{
    // =====================================================
    // PASO 1: NORMALIZAR ESTRUCTURA DE ESTADO
    // =====================================================
    
    if (!isset($pedido['estado']) && isset($pedido['estadoNombre'])) {
        $pedido['estado'] = [
            'estId' => $pedido['estId'] ?? 1,
            'estNombre' => $pedido['estadoNombre']
        ];
    }

    if (!isset($pedido['personalizacion']) && isset($pedido['perId']) && $pedido['perId'] !== null) {
        $pedido['personalizacion'] = [
            'perId' => $pedido['perId']
        ];
    }
    
    // =====================================================
    // PASO 2: OBTENER DETALLES DEL CLIENTE
    // =====================================================
    
    $token = Session::get('jwt_token');
    $clienteInfo = null;

    // A. El cliente es REGISTRADO
    if (!empty($pedido['usuIdCliente']) && $pedido['usuIdCliente'] !== null && $token) {
        try {
            $clienteInfo = $this->apiService->get("/usuarios/{$pedido['usuIdCliente']}", [
                'headers' => ['Authorization' => 'Bearer ' . $token]
            ]);
            
            if (is_array($clienteInfo)) {
                $clienteInfo = $this->normalizarClienteKeys($clienteInfo, 'usuario');
                $clienteInfo['tipo'] = 'usuario_registrado';
            }
            
        } catch (\Exception $e) {
            Log::warning("Error al buscar detalles de Usuario ID: {$pedido['usuIdCliente']}", [
                'error' => $e->getMessage()
            ]);
        }
    } 
    
    // B. El pedido tiene un Contacto de Origen
    elseif (!empty($pedido['conId']) && $pedido['conId'] !== null && $token) {
        try {
            $clienteInfo = $this->apiService->get("/contactos/{$pedido['conId']}", [
                'headers' => ['Authorization' => 'Bearer ' . $token]
            ]);
            
            if (is_array($clienteInfo)) {
                $clienteInfo = $this->normalizarClienteKeys($clienteInfo, 'contacto');
                $clienteInfo['tipo'] = 'contacto_externo';
            }
            
        } catch (\Exception $e) {
            Log::warning("Error al buscar detalles de Contacto ID: {$pedido['conId']}", [
                'error' => $e->getMessage()
            ]);
        }
    }
    
    // C. Si no tiene ni usuario ni contacto, usar el nombre básico
    else {
        if (!empty($pedido['nombreCliente'])) {
            $clienteInfo = [
                'tipo' => 'sin_detalles',
                'nombre' => $pedido['nombreCliente'],
                'identificador' => $pedido['pedIdentificadorCliente'] ?? null
            ];
        }
    }
    
    $pedido['clienteDetalles'] = $clienteInfo;

    return $pedido;
}
    /**
     * Normaliza las claves del cliente de snake_case a camelCase
     * @param array $data Datos del cliente/contacto
     * @param string $tipo 'usuario' o 'contacto'
     * @return array Datos normalizados
     */
    private function normalizarClienteKeys(array $data, string $tipo): array
    {
        if ($tipo === 'usuario') {
            // El API de Spring Boot devuelve: id, nombre, correo, telefono, docnum, activo
            return [
                'usuId' => $data['id'] ?? $data['usu_id'] ?? $data['usuId'] ?? null,
                'usuNombre' => $data['nombre'] ?? $data['usu_nombre'] ?? $data['usuNombre'] ?? null,
                'usuCorreo' => $data['correo'] ?? $data['usu_correo'] ?? $data['usuCorreo'] ?? null,
                'usuTelefono' => $data['telefono'] ?? $data['usu_telefono'] ?? $data['usuTelefono'] ?? null,
                'usuDocnum' => $data['docnum'] ?? $data['usu_docnum'] ?? $data['usuDocnum'] ?? null,
                'usuActivo' => $data['activo'] ?? $data['usu_activo'] ?? $data['usuActivo'] ?? true,
            ];
        } elseif ($tipo === 'contacto') {
            // El API de Spring Boot devuelve: id, nombre, correo, telefono, mensaje, estado
            return [
                'conId' => $data['id'] ?? $data['con_id'] ?? $data['conId'] ?? null,
                'conNombre' => $data['nombre'] ?? $data['con_nombre'] ?? $data['conNombre'] ?? null,
                'conCorreo' => $data['correo'] ?? $data['con_correo'] ?? $data['conCorreo'] ?? null,
                'conTelefono' => $data['telefono'] ?? $data['con_telefono'] ?? $data['conTelefono'] ?? null,
                'conMensaje' => $data['mensaje'] ?? $data['con_mensaje'] ?? $data['conMensaje'] ?? null,
                'conEstado' => $data['estado'] ?? $data['con_estado'] ?? $data['conEstado'] ?? null,
            ];
        }
        
        return $data; // Devolver sin cambios si el tipo no coincide
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
     * PATCH /admin/pedidos/{id}/asignar-empleado
     */
    public function asignarEmpleado(Request $request, $pedidoId)
    {
        $request->validate([
            'usuIdEmpleado' => 'required|integer' // Validamos el ID del empleado
        ]);

        try {
            $data = [
                'usuIdEmpleado' => $request->input('usuIdEmpleado') // Obtenemos el ID del body JSON
            ];
            
            // 🔥 CRÍTICO: Llamada PATCH a Spring Boot con el endpoint ESPECÍFICO
            $response = $this->apiService->patch("/pedidos/{$pedidoId}/asignar", $data, [
                'headers' => ['Authorization' => 'Bearer ' . Session::get('jwt_token')]
            ]);

            if ($response === null) {
                // Es mejor lanzar la excepción con el mensaje de Spring Boot si es posible
                throw new \Exception('API Error: No se pudo actualizar el pedido.');
            }

            return response()->json([
                'success' => true,
                'message' => 'Diseñador asignado con éxito.',
                'pedido' => $response
            ]);

        } catch (\Exception $e) {
            // ... (manejo de errores) ...
            // El error 500 ahora será manejado por la lógica de ResourceNotFoundException
            return response()->json(['success' => false, 'message' => 'Error en el servidor al asignar empleado: ' . $e->getMessage()], 500);
        }
    }

    /**
     * Obtener lista de estados disponibles, usando nombres amigables.
     */
    private function getEstadosDisponibles(): array
    {
        // Los IDs deben coincidir con la BD y el orden del array de la BD.
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

    private function getEstadoMapeo()
    {
        // Mapeo del nombre crudo de la BD (snake_case) al texto amigable deseado
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
            'desconocido' => 'Estado Desconocido' // Default
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

    public function verArchivo($path)
    {
        $baseUrl = "http://localhost:8080/"; 
        $url = $baseUrl . $path;

        try {
            $response = Http::get($url);

            if ($response->successful()) {
                return response($response->body(), 200)
                    ->header('Content-Type', $response->header('Content-Type'))
                    ->header('Cache-Control', 'public, max-age=86400'); // 🔥 AÑADIR ESTA LÍNEA
            }
        } catch (\Exception $e) {
            Log::error("Error al conectar con Spring Boot para archivo: " . $e->getMessage());
        }

        abort(404, 'La imagen no existe en el servidor de joyería (Spring Boot).');
    }

    public function subirDiseno(Request $request, $id)
    {
        // 🔥 VALIDACIÓN BÁSICA (solo tamaño)
        $request->validate([
            'diseno_archivo' => 'required|file|max:51200' // 50 MB
        ]);

        // 🔥 VALIDACIÓN MANUAL DE EXTENSIÓN
        $file = $request->file('diseno_archivo');
        $extension = strtolower($file->getClientOriginalExtension());
        $allowedExtensions = ['glb', 'gltf', 'png', 'jpg', 'jpeg', 'webp'];
        
        if (!in_array($extension, $allowedExtensions)) {
            return response()->json([
                'success' => false,
                'message' => 'Formato no permitido. Extensiones válidas: ' . implode(', ', $allowedExtensions)
            ], 422);
        }

        try {
            $response = $this->apiService->attachFile(
                "/pedidos/{$id}/subir-render-oficial", 
                [], 
                $file, 
                'archivo',
                ['headers' => ['Authorization' => 'Bearer ' . session('jwt_token')]] 
            );

            if ($response) {
                return response()->json([
                    'success' => true,
                    'message' => 'Diseño cargado correctamente y registrado en el historial.'
                ]);
            }

            return response()->json([
                'success' => false, 
                'message' => 'La API de Java no devolvió una respuesta exitosa.'
            ], 400);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false, 
                'message' => 'Error de conexión: ' . $e->getMessage()
            ], 500);
        }
    }

    // 🔥 MÉTODO AUXILIAR NUEVO
    private function detectarTipoArchivo($path)
    {
        if (!$path) return 'imagen';
        
        $extension = strtolower(pathinfo($path, PATHINFO_EXTENSION));
        
        return in_array($extension, ['glb', 'gltf']) ? 'modelo3d' : 'imagen';
    }

    /**
     * Sube una foto del producto real (Fase 3).
     * POST /admin/pedidos/{id}/subir-producto-final
     */
    public function subirProductoFinal(Request $request, $id)
    {
        // 1. Validar que sea una imagen
        $request->validate([
            'producto_foto' => 'required|image|mimes:jpeg,png,jpg,webp|max:10240',
        ]);

        try {
            // 2. Capturar el objeto del archivo
            $file = $request->file('producto_foto');

            /**
             * 3. Llamada usando el orden posicional que ya te funciona en 'subirDiseno':
             * Arg 1: Endpoint
             * Arg 2: Data (Array vacío)
             * Arg 3: El objeto del archivo ($file)
             * Arg 4: El nombre del parámetro que espera Java ('archivo')
             * Arg 5: Opciones (Headers)
             */
            $response = $this->apiService->attachFile(
                "/fotos/subir/{$id}", 
                [], 
                $file, 
                'archivo',
                ['headers' => ['Authorization' => 'Bearer ' . Session::get('jwt_token')]] 
            );

            return response()->json([
                'success' => true,
                'message' => 'Foto del producto final cargada exitosamente.'
            ]);

        } catch (\Exception $e) {
            \Log::error("Fallo en subirProductoFinal: " . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Error técnico: ' . $e->getMessage()
            ], 500);
        }
    }
}