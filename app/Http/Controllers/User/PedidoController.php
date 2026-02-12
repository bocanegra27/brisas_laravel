<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use App\Services\ApiService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Log;

/**
 * Controlador de Pedidos para Usuarios (Clientes)
 * 
 * Maneja la visualización de los pedidos del usuario autenticado
 */
class PedidoController extends Controller
{
    private ApiService $apiService;

    public function __construct(ApiService $apiService)
    {
        $this->apiService = $apiService;
    }

    /**
     * Mostrar listado de pedidos del usuario autenticado
     * GET /user/pedidos
     */
    public function index(Request $request)
    {
        try {
            $userId = Session::get('user_id');
            
            if (!$userId) {
                return redirect()->route('login')
                    ->with('error', 'Debes iniciar sesión para ver tus pedidos.');
            }

            // Obtener parámetros de paginación
            $page = $request->get('page', 0);
            $size = $request->get('size', 10);
            $estadoId = $request->get('estadoId');

            // Construir query params
            $params = [
                'page' => $page,
                'size' => $size,
                'clienteId' => $userId // Filtrar por cliente
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
                Log::error('User\\PedidoController: Error al obtener pedidos del API');
                return view('user.pedidos.index')->with([
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
                ])->with('error', 'Error al cargar tus pedidos.');
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

            return view('user.pedidos.index', $data);

        } catch (\Exception $e) {
            Log::error('User\\PedidoController@index: Excepción', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return view('user.pedidos.index')->with([
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
            ])->with('error', 'Error al cargar tus pedidos. Por favor, intenta nuevamente.');
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

        return $pedido;
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
