<?php

namespace App\Http\Controllers\Designer;

use App\Http\Controllers\Controller;
use App\Services\ApiService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Http;

/**
 * Controlador de Pedidos para Diseñadores
 * 
 * Maneja la visualización de los pedidos asignados al diseñador autenticado
 */
class PedidoController extends Controller
{
    private ApiService $apiService;

    public function __construct(ApiService $apiService)
    {
        $this->apiService = $apiService;
    }

    private function buildStatsFromPedidos(array $pedidos): array
    {
        $porEstado = [];
        foreach ($this->getEstadosDisponibles() as $estado) {
            $estadoId = (int) ($estado['id'] ?? 0);
            if ($estadoId > 0) {
                $porEstado[$estadoId] = 0;
            }
        }

        foreach ($pedidos as $pedido) {
            $estadoId = (int) ($pedido['estId'] ?? $pedido['est_id'] ?? 0);
            if ($estadoId > 0) {
                $porEstado[$estadoId] = (int) (($porEstado[$estadoId] ?? 0) + 1);
            }
        }

        $total = array_sum($porEstado);
        $finalizados = (int) ($porEstado[9] ?? 0);
        $cancelados = (int) ($porEstado[10] ?? 0);
        $totalActivos = $total - $finalizados - $cancelados;

        return [
            'total' => $total,
            'totalActivos' => $totalActivos,
            'finalizados' => $finalizados,
            'cancelados' => $cancelados,
            'porEstado' => $porEstado,

            'pendientes' => (int) ($porEstado[1] ?? 0),
            'confirmados' => (int) ($porEstado[2] ?? 0),
            'produccion' => (int) ($porEstado[5] ?? 0),
            'entregados' => (int) ($porEstado[9] ?? 0)
        ];
    }

    /**
     * Mostrar listado de pedidos asignados al diseñador autenticado
     * GET /designer/pedidos
     */
    public function index(Request $request)
    {
        try {
            $designerId = Session::get('user_id');
            
            // 🔥 DEBUG SIMPLE Y CLARO
            error_log("=== DESIGNER PEDIDOS DEBUG ===");
            error_log("Designer ID: " . $designerId);
            error_log("JWT Token: " . (Session::get('jwt_token') ? 'EXISTS' : 'NULL'));
            
            if (!$designerId) {
                error_log("ERROR: No designer ID found");
                return redirect()->route('login')
                    ->with('error', 'Debes iniciar sesión para ver tus pedidos asignados.');
            }

            // Obtener parámetros de paginación
            $page = $request->get('page', 0);
            $size = $request->get('size', 10);
            $estadoId = $request->get('estadoId');

            // 🔥 CORRECCIÓN: Designer ve SOLO sus pedidos asignados
            // Endpoint específico: /api/pedidos/empleado/{designerId}
            $endpoint = "/pedidos/empleado/{$designerId}";
            
            error_log("Endpoint: " . $endpoint);
            error_log("Designer ID: " . $designerId . " (viendo SOLO sus pedidos asignados)");

            // Llamada al API
            $response = $this->apiService->get($endpoint, [
                'headers' => [
                    'Authorization' => 'Bearer ' . Session::get('jwt_token')
                ]
            ]);

            error_log("API Response Type: " . gettype($response));
            error_log("API Response Count: " . (is_array($response) ? count($response) : 'NOT_ARRAY'));
            
            // 🔥 DEBUG DEL PRIMER PEDIDO
            if (is_array($response) && count($response) > 0) {
                $primerPedido = $response[0];
                error_log("=== PRIMER PEDIDO RAW ===");
                error_log("Keys: " . json_encode(array_keys($primerPedido)));
                
                // Verificar campos específicos
                $camposCriticos = ['pedId', 'pedCodigo', 'estId', 'estadoNombre', 'nombreCliente', 'nombreEmpleado'];
                foreach ($camposCriticos as $campo) {
                    $valor = $primerPedido[$campo] ?? 'MISSING';
                    error_log("{$campo}: {$valor}");
                }
            }

            // Verificar respuesta
            if ($response === null) {
                error_log("ERROR: API returned null");
                $stats = $this->getEstadisticasVacias();
                return view('designer.pedidos.index')->with([
                    'pedidos' => [],
                    'totalElements' => 0,
                    'totalPages' => 0,
                    'currentPage' => 0,
                    'pageSize' => $size,
                    'estados' => $this->getEstadosDisponibles(),
                    'estadoMapeo' => $this->getEstadoMapeo(),
                    'stats' => $stats,
                    'filtros' => [
                        'estadoId' => $estadoId
                    ]
                ])->with('error', 'Error al cargar tus pedidos asignados.');
            }

            $stats = $this->buildStatsFromPedidos(is_array($response) ? $response : []);

            // 🔥 FILTRADO LOCAL: Si se solicitó filtro por estado, aplicarlo después de obtener los pedidos
            $pedidosFiltrados = $response;
            if ($estadoId !== null && $estadoId !== '' && is_array($response)) {
                $pedidosFiltrados = array_filter($response, function($pedido) use ($estadoId) {
                    return isset($pedido['estId']) && $pedido['estId'] == $estadoId;
                });
                error_log("Filtered by estado {$estadoId}: " . count($pedidosFiltrados) . " pedidos");
            }

            // 🔥 MAPEO DE CAMPOS: Mantener nombres originales que el componente espera
            $pedidosMapeados = array_map(function($pedido) {
                return [
                    // Mantener nombres originales para compatibilidad con componente
                    'pedId' => $pedido['pedId'] ?? null,
                    'pedCodigo' => $pedido['pedCodigo'] ?? null,
                    'pedFechaCreacion' => $pedido['pedFechaCreacion'] ?? null,
                    'pedComentarios' => $pedido['pedComentarios'] ?? null,
                    'estId' => $pedido['estId'] ?? null,
                    'estadoNombre' => $pedido['estadoNombre'] ?? null,
                    'usuIdEmpleado' => $pedido['usuIdEmpleado'] ?? null,
                    'nombreEmpleado' => $pedido['nombreEmpleado'] ?? 'No asignado',
                    'usuIdCliente' => $pedido['usuIdCliente'] ?? null,
                    'nombreCliente' => $pedido['nombreCliente'] ?? 'No especificado',
                    'perId' => $pedido['perId'] ?? null,
                    'renderPath' => $pedido['renderPath'] ?? null,
                    'fotosFinales' => $pedido['fotosFinales'] ?? [],
                    'pedIdentificadorCliente' => $pedido['pedIdentificadorCliente'] ?? null,
                    'conId' => $pedido['conId'] ?? null,
                    'sesionId' => $pedido['sesionId'] ?? null,
                    // También incluir snake_case por si acaso
                    'ped_id' => $pedido['pedId'] ?? null,
                    'ped_codigo' => $pedido['pedCodigo'] ?? null,
                    'ped_fecha_creacion' => $pedido['pedFechaCreacion'] ?? null,
                    'ped_comentarios' => $pedido['pedComentarios'] ?? null,
                    'est_id' => $pedido['estId'] ?? null,
                    'estado_nombre' => $pedido['estadoNombre'] ?? null,
                    'usu_id_empleado' => $pedido['usuIdEmpleado'] ?? null,
                    'nombre_empleado' => $pedido['nombreEmpleado'] ?? 'No asignado',
                    'usu_id_cliente' => $pedido['usuIdCliente'] ?? null,
                    'nombre_cliente' => $pedido['nombreCliente'] ?? 'No especificado',
                ];
            }, $pedidosFiltrados);

            error_log("Final pedidos count (mapeados): " . count($pedidosMapeados));
            
            // 🔥 DEBUG: Verificar primer pedido mapeado
            if (count($pedidosMapeados) > 0) {
                error_log("=== PRIMER PEDIDO MAPEADO ===");
                error_log("Datos: " . json_encode($pedidosMapeados[0]));
            }

            return view('designer.pedidos.index')->with([
                'pedidos' => $pedidosMapeados,
                'totalElements' => count($pedidosMapeados),
                'totalPages' => 1, // Simplificado ya que el backend no maneja paginación en este endpoint
                'currentPage' => 0,
                'pageSize' => $size,
                'estados' => $this->getEstadosDisponibles(),
                'estadoMapeo' => $this->getEstadoMapeo(),
                'stats' => $stats,
                'filtros' => [
                    'estadoId' => $estadoId
                ]
            ]);

        } catch (\Exception $e) {
            error_log("EXCEPTION: " . $e->getMessage());
            $stats = $this->getEstadisticasVacias();
            return view('designer.pedidos.index')->with([
                'pedidos' => [],
                'totalElements' => 0,
                'totalPages' => 0,
                'currentPage' => 0,
                'pageSize' => 10,
                'estados' => $this->getEstadosDisponibles(),
                'estadoMapeo' => $this->getEstadoMapeo(),
                'stats' => $stats,
                'filtros' => [
                    'estadoId' => null
                ]
            ])->with('error', 'Error al cargar tus pedidos: ' . $e->getMessage());
        }
    }

    /**
     * Obtener detalles completos del pedido para la vista de gestión
     * GET /designer/pedidos/{id}/detalles
     */
    public function detalles($id)
    {
        try {
            $response = $this->apiService->get("/pedidos/{$id}", [
                'headers' => [
                    'Authorization' => 'Bearer ' . Session::get('jwt_token')
                ]
            ]);

            if ($response === null || !is_array($response)) {
                Log::error('Designer\PedidoController@detalles: Error al obtener detalles', [
                    'id' => $id,
                    'response' => $response
                ]);
                return response()->json(['success' => false, 'message' => 'Error al cargar los detalles'], 500);
            }

            // Enriquecer el pedido con renders y fotos
            $pedidoEnriquecido = $this->enriquecerPedido($response);

            Log::info('Designer\PedidoController@detalles: Pedido enriquecido', [
                'id' => $id,
                'renderPath' => $pedidoEnriquecido['renderPath'] ?? 'NO_RENDER',
                'fotosFinales' => isset($pedidoEnriquecido['fotosFinales']) ? count($pedidoEnriquecido['fotosFinales']) : 'NO_FOTOS'
            ]);

            return response()->json([
                'success' => true,
                'pedido' => $pedidoEnriquecido
            ]);

        } catch (\Exception $e) {
            Log::error('Designer\PedidoController@detalles: Excepción', [
                'id' => $id,
                'error' => $e->getMessage()
            ]);

            return response()->json(['success' => false, 'message' => 'Error al cargar los detalles'], 500);
        }
    }

    /**
     * Enriquecer un pedido con información adicional
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

        // Normalizar personalización
        if (!isset($pedido['personalizacion']) && isset($pedido['perId']) && $pedido['perId'] !== null) {
            $pedido['personalizacion'] = [
                'perId' => $pedido['perId']
            ];
        }

        // Obtener renders 3D del pedido
        try {
            $renderResponse = $this->apiService->get("/pedidos/{$pedido['pedId']}/render3d", [
                'headers' => [
                    'Authorization' => 'Bearer ' . Session::get('jwt_token')
                ]
            ]);

            // Log simple para evitar corrupción
            error_log("Render Response: " . print_r($renderResponse, true));

            if ($renderResponse && is_array($renderResponse) && count($renderResponse) > 0) {
                // Intentar diferentes nombres de campo posibles según el backend Java
                $pedido['renderPath'] = $renderResponse[0]['renImagen'] ?? 
                                      $renderResponse[0]['ren_imagen'] ?? 
                                      $renderResponse[0]['renArchivo'] ?? 
                                      $renderResponse[0]['archivo'] ?? 
                                      $renderResponse[0]['path'] ?? 
                                      $renderResponse[0]['ruta'] ?? 
                                      $renderResponse[0]['url'] ?? null;
                                      
                error_log("RenderPath asignado: " . $pedido['renderPath']);
            }
        } catch (\Exception $e) {
            error_log("Error al obtener renders del pedido {$pedido['pedId']}: " . $e->getMessage());
        }

        // Obtener fotos finales del pedido
        try {
            $fotosResponse = $this->apiService->get("/pedidos/{$pedido['pedId']}/fotos-producto-final", [
                'headers' => [
                    'Authorization' => 'Bearer ' . Session::get('jwt_token')
                ]
            ]);

            // Log simple para evitar corrupción
            error_log("Fotos Response: " . print_r($fotosResponse, true));

            if ($fotosResponse && is_array($fotosResponse)) {
                // Mapear las fotos con diferentes nombres de campo posibles según el backend Java
                $pedido['fotosFinales'] = array_map(function($foto) {
                    return [
                        'fpfRuta' => $foto['fotImagenFinal'] ?? 
                                   $foto['fot_imagen_final'] ?? 
                                   $foto['fpfRuta'] ?? 
                                   $foto['ruta'] ?? 
                                   $foto['path'] ?? 
                                   $foto['archivo'] ?? 
                                   $foto['url'] ?? 
                                   'SIN_RUTA'
                    ];
                }, $fotosResponse);
                
                error_log("FotosFinales asignadas: " . count($pedido['fotosFinales']));
            }
        } catch (\Exception $e) {
            error_log("Error al obtener fotos finales del pedido {$pedido['pedId']}: " . $e->getMessage());
        }

        // Obtener detalles del cliente para el diseñador
        $token = Session::get('jwt_token');
        $clienteInfo = null;

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
                Log::warning("Error al buscar detalles de Cliente ID: {$pedido['usuIdCliente']}", [
                    'error' => $e->getMessage()
                ]);
            }
        } elseif (!empty($pedido['conId']) && $pedido['conId'] !== null && $token) {
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
        } else {
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
     */
    private function normalizarClienteKeys(array $data, string $tipo): array
    {
        if ($tipo === 'usuario') {
            return [
                'usuId' => $data['id'] ?? $data['usu_id'] ?? $data['usuId'] ?? null,
                'usuNombre' => $data['nombre'] ?? $data['usu_nombre'] ?? $data['usuNombre'] ?? null,
                'usuCorreo' => $data['correo'] ?? $data['usu_correo'] ?? $data['usuCorreo'] ?? null,
                'usuTelefono' => $data['telefono'] ?? $data['usu_telefono'] ?? $data['usuTelefono'] ?? null,
                'usuDocnum' => $data['docnum'] ?? $data['usu_docnum'] ?? $data['usuDocnum'] ?? null,
                'usuActivo' => $data['activo'] ?? $data['usu_activo'] ?? $data['usuActivo'] ?? true,
            ];
        } elseif ($tipo === 'contacto') {
            return [
                'conId' => $data['id'] ?? $data['con_id'] ?? $data['conId'] ?? null,
                'conNombre' => $data['nombre'] ?? $data['con_nombre'] ?? $data['conNombre'] ?? null,
                'conCorreo' => $data['correo'] ?? $data['con_correo'] ?? $data['conCorreo'] ?? null,
                'conTelefono' => $data['telefono'] ?? $data['con_telefono'] ?? $data['conTelefono'] ?? null,
                'conMensaje' => $data['mensaje'] ?? $data['con_mensaje'] ?? $data['conMensaje'] ?? null,
                'conEstado' => $data['estado'] ?? $data['con_estado'] ?? $data['conEstado'] ?? null,
            ];
        }
        
        return $data;
    }

    /**
     * Obtener lista de estados disponibles
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

    /**
     * Método temporal para probar endpoints del backend
     * GET /designer/pedidos/test/{id}
     */
    public function testBackend($id)
    {
        try {
            Log::info('=== INICIANDO PRUEBA DE BACKEND ===');
            
            // 1. Probar endpoint del pedido
            $pedidoResponse = $this->apiService->get("/pedidos/{$id}", [
                'headers' => ['Authorization' => 'Bearer ' . Session::get('jwt_token')]
            ]);
            Log::info('Pedido Response:', ['response' => $pedidoResponse]);
            
            // 2. Probar endpoint de renders
            $renderResponse = $this->apiService->get("/pedidos/{$id}/render3d", [
                'headers' => ['Authorization' => 'Bearer ' . Session::get('jwt_token')]
            ]);
            Log::info('Render Response:', ['response' => $renderResponse]);
            
            // 3. Probar endpoint de fotos
            $fotosResponse = $this->apiService->get("/pedidos/{$id}/fotos-producto-final", [
                'headers' => ['Authorization' => 'Bearer ' . Session::get('jwt_token')]
            ]);
            Log::info('Fotos Response:', ['response' => $fotosResponse]);
            
            return response()->json([
                'success' => true,
                'pedido' => $pedidoResponse,
                'renders' => $renderResponse,
                'fotos' => $fotosResponse
            ]);
            
        } catch (\Exception $e) {
            Log::error('Error en prueba de backend:', ['error' => $e->getMessage()]);
            return response()->json(['success' => false, 'error' => $e->getMessage()], 500);
        }
    }

    /**
     * Vista de gestion del pedido con Timeline.
     * GET /designer/pedidos/{id}/gestionar
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
                    ->route('designer.pedidos.index')
                    ->with('error', 'Pedido no encontrado.');
            }

            // Enriquecer el pedido
            $pedido = $this->enriquecerPedido($response);

            $estadosArray = $this->getEstadosDisponibles();
            $estados = [];
            foreach ($estadosArray as $estado) {
                $estados[$estado['id']] = $estado['nombre']; 
            }

            $estadoMapeo = $this->getEstadoMapeo();

            return view('designer.pedidos.gestionar', [
                'pedido' => $pedido,
                'estados' => $estados,
                'estadoMapeo' => $estadoMapeo,
            ]);

        } catch (\Exception $e) {
            Log::error('Designer\PedidoController@gestionar: Excepcion', [
                'id' => $id,
                'error' => $e->getMessage()
            ]);

            return redirect()
                ->route('designer.pedidos.index')
                ->with('error', 'Error al cargar el pedido.');
        }
    }

    /**
     * Actualizar estado y comentarios de un pedido
     * PUT /designer/pedidos/{id}
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

            Log::info('Designer\PedidoController: Pedido actualizado', ['id' => $id]);

            return redirect()
                ->route('designer.pedidos.index')
                ->with('success', 'Pedido actualizado exitosamente.');

        } catch (\Illuminate\Validation\ValidationException $e) {
            return back()
                ->withErrors($e->errors())
                ->withInput();
        } catch (\Exception $e) {
            Log::error('Designer\PedidoController@update: Excepcion', [
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
     * PATCH /designer/pedidos/{id}/estado-historial
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
            // Si no hay archivo, usamos un POST normal al endpoint de historial
            $response = $this->apiService->patch("/pedidos/{$id}/estado", [
                'nuevoEstadoId' => (int)$request->estadoId,
                'comentarios' => $request->comentarios
            ], [
                'headers' => ['Authorization' => 'Bearer ' . session('jwt_token')]
            ]);
        }

        if ($response) {
            return response()->json(['success' => true, 'message' => 'Estado actualizado correctamente.']);
        }

        return response()->json(['success' => false, 'message' => 'Error al comunicar con el servidor de joyería.'], 500);
    }

    /**
     * Obtiene el historial de un pedido para la vista de gestión.
     * GET /designer/pedidos/{id}/historial
     */
    public function obtenerHistorial($pedidoId)
    {
        try {
            Log::info('=== INICIANDO OBTENER HISTORIAL ===');
            Log::info('Pedido ID:', ['id' => $pedidoId]);
            Log::info('JWT Token:', ['token' => Session::get('jwt_token') ? 'EXISTS' : 'NULL']);
            
            // Llamada al endpoint de Spring Boot para obtener el historial
            $endpoint = "/pedidos/{$pedidoId}/historial";
            $fullUrl = config('services.spring_api.url', 'http://localhost:8080/api') . $endpoint;
            Log::info('Endpoint completo:', ['url' => $fullUrl]);
            
            $response = $this->apiService->get($endpoint, [
                'headers' => ['Authorization' => 'Bearer ' . Session::get('jwt_token')]
            ]);

            Log::info('Respuesta del API:', [
                'response' => $response,
                'isNull' => $response === null,
                'isArray' => is_array($response),
                'type' => gettype($response)
            ]);

            if ($response === null || !is_array($response)) {
                Log::error('Designer\PedidoController@obtenerHistorial: Error al obtener historial', [
                    'pedidoId' => $pedidoId,
                    'response' => $response
                ]);
                return response()->json([
                    'success' => false, 
                    'message' => 'Error al obtener el historial.'
                ], 500);
            }
            
            // Spring Boot devuelve una lista ordenada de DTOs del historial
            Log::info('Historial obtenido exitosamente:', ['count' => count($response)]);
            
            return response()->json([
                'success' => true, 
                'historial' => $response
            ]);

        } catch (\Exception $e) {
            Log::error('Designer\PedidoController@obtenerHistorial: Excepción.', [
                'pedidoId' => $pedidoId,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            return response()->json([
                'success' => false, 
                'message' => 'Error interno al cargar el historial.'
            ], 500);
        }
    }

    /**
     * Subir diseño/render del pedido
     * POST /designer/pedidos/{id}/subir-diseno
     */
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

    /**
     * Sube una foto del producto real
     * POST /designer/pedidos/{id}/subir-producto-final
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

    /**
     * Ver archivo desde Spring Boot (render, imagen, etc.)
     * GET /designer/pedidos/ver-archivo/{path}
     */
    public function verArchivo(Request $request, $path)
    {
        $baseUrl = "http://localhost:8080/"; 
        $url = $baseUrl . $path;

        try {
            $response = Http::get($url);

            if ($response->successful()) {
                return response($response->body(), 200)
                    ->header('Content-Type', $response->header('Content-Type'))
                    ->header('Cache-Control', 'public, max-age=86400');
            }
        } catch (\Exception $e) {
            Log::error("Error al obtener archivo: " . $e->getMessage());
            abort(404, 'Archivo no encontrado');
        }
    }

    /**
     * Obtener estadísticas de pedidos para el diseñador
     */
    private function getEstadisticas(): array
    {
        try {
            $designerId = Session::get('user_id');
            $token = Session::get('jwt_token');
            $headers = ['headers' => ['Authorization' => 'Bearer ' . $token]];

            $porEstado = [];
            foreach ($this->getEstadosDisponibles() as $estado) {
                $estadoId = (int) ($estado['id'] ?? 0);
                if ($estadoId <= 0) {
                    continue;
                }

                $response = $this->apiService->get("/pedidos/count?estadoId={$estadoId}&empleadoId={$designerId}", $headers);
                $porEstado[$estadoId] = (int) ($response['count'] ?? 0);
            }

            $total = array_sum($porEstado);
            $finalizados = (int) ($porEstado[9] ?? 0);
            $cancelados = (int) ($porEstado[10] ?? 0);
            $totalActivos = $total - $finalizados - $cancelados;

            return [
                'total' => $total,
                'totalActivos' => $totalActivos,
                'finalizados' => $finalizados,
                'cancelados' => $cancelados,
                'porEstado' => $porEstado,

                'pendientes' => (int) ($porEstado[1] ?? 0),
                'confirmados' => (int) ($porEstado[2] ?? 0),
                'produccion' => (int) ($porEstado[5] ?? 0),
                'entregados' => (int) ($porEstado[9] ?? 0)
            ];

        } catch (\Exception $e) {
            Log::error('Designer\PedidoController@getEstadisticas: Excepcion', [
                'error' => $e->getMessage()
            ]);

            return $this->getEstadisticasVacias();
        }
    }

    /**
     * Estadísticas vacías
     */
    private function getEstadisticasVacias(): array
    {
        return [
            'total' => 0,
            'totalActivos' => 0,
            'finalizados' => 0,
            'cancelados' => 0,
            'porEstado' => [],
            'pendientes' => 0,
            'confirmados' => 0,
            'produccion' => 0,
            'entregados' => 0
        ];
    }

    /**
     * Obtener mapeo de estados
     */
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
}
