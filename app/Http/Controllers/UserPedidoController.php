<?php

namespace App\Http\Controllers;

use App\Services\ApiService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Log;

/**
 * Controlador de Pedidos para Usuarios (Clientes)
 * 
 * Maneja la visualización de los pedidos del usuario autenticado
 */
class UserPedidoController extends Controller
{
    private ApiService $apiService;

    public function __construct(ApiService $apiService)
    {
        $this->apiService = $apiService;
    }

    public function show($id)
    {
        try {
            $userId = Session::get('user_id');

            if (!$userId) {
                return redirect()->route('login')
                    ->with('error', 'Debes iniciar sesión para ver el detalle del pedido.');
            }

            $pedido = $this->buildPedidoDetalles($id);

            return view('user.pedidos.show', [
                'pedido' => $pedido,
            ]);

        } catch (\Exception $e) {
            Log::error('UserPedidoController@show: Excepción', [
                'error' => $e->getMessage(),
                'pedido_id' => $id,
            ]);

            return redirect()->route('user.pedidos.index')
                ->with('error', 'Error al cargar el detalle del pedido.');
        }
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

            // Llamar a la API real para obtener los pedidos del usuario
            $response = $this->apiService->get('/pedidos/cliente/' . $userId, [
                'headers' => [
                    'Authorization' => 'Bearer ' . Session::get('jwt_token')
                ]
            ]);

            // Procesar respuesta de la API
            $pedidos = [];
            if ($response !== null && is_array($response)) {
                $pedidos = $response;
            }

            // Preparar datos para la vista
            $data = [
                'pedidos' => $pedidos,
                'totalElements' => count($pedidos),
                'totalPages' => count($pedidos) > 0 ? 1 : 0,
                'currentPage' => 0,
                'pageSize' => 10,
                'estados' => $this->getEstadosDisponibles(),
                'estadoMapeo' => $this->getEstadoMapeo(),
                'filtros' => [
                    'estadoId' => null
                ]
            ];

            return view('user.pedidos.index', $data);

        } catch (\Exception $e) {
            Log::error('UserPedidoController@index: Excepción', [
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
     * Mostrar detalles completos de un pedido específico
     * GET /user/pedidos/{id}/detalles
     */
    public function detalles($id)
    {
        try {
            $userId = Session::get('user_id');
            
            if (!$userId) {
                return response()->json(['error' => 'No autorizado'], 401);
            }

            $pedido = $this->buildPedidoDetalles($id);

            return response()->json($pedido);

        } catch (\Exception $e) {
            Log::error('UserPedidoController@detalles: Excepción', [
                'error' => $e->getMessage(),
                'pedido_id' => $id
            ]);

            return response()->json(['error' => 'Error al cargar los detalles del pedido'], 500);
        }
    }

    private function buildPedidoDetalles($id): array
    {
        // Obtener pedido principal
        $pedidoResponse = $this->apiService->get('/pedidos/' . $id, [
            'headers' => [
                'Authorization' => 'Bearer ' . Session::get('jwt_token'),
            ],
        ]);

        if ($pedidoResponse === null) {
            throw new \RuntimeException('Pedido no encontrado');
        }

        // Obtener historial
        $historialResponse = $this->apiService->get('/pedidos/' . $id . '/historial', [
            'headers' => [
                'Authorization' => 'Bearer ' . Session::get('jwt_token'),
            ],
        ]);

        // Obtener renders 3D específicamente
        $renderResponse = $this->apiService->get('/pedidos/' . $id . '/render3d', [
            'headers' => [
                'Authorization' => 'Bearer ' . Session::get('jwt_token'),
            ],
        ]);

        // Obtener fotos finales específicamente
        $fotosResponse = $this->apiService->get('/pedidos/' . $id . '/fotos-producto-final', [
            'headers' => [
                'Authorization' => 'Bearer ' . Session::get('jwt_token'),
            ],
        ]);

        // Construir datos completos
        $pedido = $pedidoResponse;
        $pedido['historial'] = $historialResponse ?? [];
        
        // Procesar render - tomar el primero si hay múltiples
        if ($renderResponse && is_array($renderResponse) && count($renderResponse) > 0) {
            $pedido['renderPath'] = $renderResponse[0]['renImagen'] ?? null;
        } else {
            $pedido['renderPath'] = null;
        }
        
        // Procesar fotos finales
        $pedido['fotosFinales'] = $fotosResponse ?? [];

        return $pedido;
    }

    /**
     * Enriquecer un pedido con información adicional (igual que el admin)
     */
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
