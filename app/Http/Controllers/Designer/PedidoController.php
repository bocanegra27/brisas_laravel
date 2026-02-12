<?php

namespace App\Http\Controllers\Designer;

use App\Http\Controllers\Controller;
use App\Services\ApiService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Log;

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

    /**
     * Mostrar listado de pedidos asignados al diseñador autenticado
     * GET /designer/pedidos
     */
    public function index(Request $request)
    {
        try {
            $designerId = Session::get('user_id');
            
            if (!$designerId) {
                return redirect()->route('login')
                    ->with('error', 'Debes iniciar sesión para ver tus pedidos asignados.');
            }

            // Obtener parámetros de paginación
            $page = $request->get('page', 0);
            $size = $request->get('size', 10);
            $estadoId = $request->get('estadoId');

            // Construir query params
            $params = [
                'page' => $page,
                'size' => $size,
                'empleadoId' => $designerId // Filtrar por diseñador/empleado
            ];

            if ($estadoId !== null && $estadoId !== '') {
                $params['estadoId'] = $estadoId;
            }

            // Construir URL con query params
            $queryString = http_build_query($params);
            $endpoint = '/pedidos?' . $queryString;

            // Llamada al API
            $response = $this->apiService->get($endpoint, [
                'headers' => [
                    'Authorization' => 'Bearer ' . Session::get('jwt_token')
                ]
            ]);

            // Verificar respuesta
            if ($response === null) {
                Log::error('Designer\\PedidoController: Error al obtener pedidos del API');
                return view('designer.pedidos.index')->with([
                    'pedidos' => [],
                    'totalElements' => 0,
                    'totalPages' => 0,
                    'currentPage' => 0,
                    'pageSize' => $size,
                    'estados' => $this->getEstadosDisponibles(),
                    'estadoMapeo' => $this->getEstadoMapeo(),
                    'filtros' => [
                        'estadoId' => $estadoId
                    ]
                ])->with('error', 'Error al cargar tus pedidos asignados.');
            }

            // Procesar respuesta
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

            // Enriquecer pedidos con información procesada
            $pedidos = array_map(function($pedido) {
                return $this->enriquecerPedido($pedido);
            }, $pedidos);

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
                'estados' => $estados,
                'estadoMapeo' => $estadoMapeo,
                'filtros' => [
                    'estadoId' => $estadoId
                ]
            ];

            return view('designer.pedidos.index', $data);

        } catch (\Exception $e) {
            Log::error('Designer\\PedidoController@index: Excepción', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return view('designer.pedidos.index')->with([
                'pedidos' => [],
                'totalElements' => 0,
                'totalPages' => 0,
                'currentPage' => 0,
                'pageSize' => 10,
                'estados' => $this->getEstadosDisponibles(),
                'estadoMapeo' => $this->getEstadoMapeo(),
                'filtros' => [
                    'estadoId' => null
                ]
            ])->with('error', 'Error al cargar tus pedidos asignados. Por favor, intenta nuevamente.');
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
