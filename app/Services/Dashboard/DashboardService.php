<?php

namespace App\Services\Dashboard;

use App\Services\ApiService;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Log;

class DashboardService
{
    protected $apiService;

    public function __construct(ApiService $apiService)
    {
        $this->apiService = $apiService;
    }

    /**
     * Obtiene estadísticas del dashboard de administrador
     * 
     * @return array
     */
    public function getAdminStats(): array
    {
        try {
            // ✅ Usuarios
            $responseActivos = $this->apiService->get('/usuarios/count?activo=true', [
                'headers' => ['Authorization' => 'Bearer ' . Session::get('jwt_token')]
            ]);

            $responseInactivos = $this->apiService->get('/usuarios/count?activo=false', [
                'headers' => ['Authorization' => 'Bearer ' . Session::get('jwt_token')]
            ]);

            // ✅ Contactos/Mensajes
            $responsePendientes = $this->apiService->get('/contactos/count?estado=pendiente', [
                'headers' => ['Authorization' => 'Bearer ' . Session::get('jwt_token')]
            ]);

            $responseAtendidos = $this->apiService->get('/contactos/count?estado=atendido', [
                'headers' => ['Authorization' => 'Bearer ' . Session::get('jwt_token')]
            ]);

            $responseArchivados = $this->apiService->get('/contactos/count?estado=archivado', [
                'headers' => ['Authorization' => 'Bearer ' . Session::get('jwt_token')]
            ]);

            // ✅ Pedidos por Estado (NUEVOS)
            $responseCotizacion = $this->apiService->get('/pedidos/count?estadoId=1', [
                'headers' => ['Authorization' => 'Bearer ' . Session::get('jwt_token')]
            ]);

            $responsePagoPendiente = $this->apiService->get('/pedidos/count?estadoId=2', [
                'headers' => ['Authorization' => 'Bearer ' . Session::get('jwt_token')]
            ]);

            $responseDisenoEnProceso = $this->apiService->get('/pedidos/count?estadoId=3', [
                'headers' => ['Authorization' => 'Bearer ' . Session::get('jwt_token')]
            ]);

            $responseDisenoAprobado = $this->apiService->get('/pedidos/count?estadoId=4', [
                'headers' => ['Authorization' => 'Bearer ' . Session::get('jwt_token')]
            ]);

            $responseTallado = $this->apiService->get('/pedidos/count?estadoId=5', [
                'headers' => ['Authorization' => 'Bearer ' . Session::get('jwt_token')]
            ]);

            $responseEngaste = $this->apiService->get('/pedidos/count?estadoId=6', [
                'headers' => ['Authorization' => 'Bearer ' . Session::get('jwt_token')]
            ]);

            $responsePulido = $this->apiService->get('/pedidos/count?estadoId=7', [
                'headers' => ['Authorization' => 'Bearer ' . Session::get('jwt_token')]
            ]);

            $responseInspeccion = $this->apiService->get('/pedidos/count?estadoId=8', [
                'headers' => ['Authorization' => 'Bearer ' . Session::get('jwt_token')]
            ]);

            $responseFinalizado = $this->apiService->get('/pedidos/count?estadoId=9', [
                'headers' => ['Authorization' => 'Bearer ' . Session::get('jwt_token')]
            ]);

            $responseCancelado = $this->apiService->get('/pedidos/count?estadoId=10', [
                'headers' => ['Authorization' => 'Bearer ' . Session::get('jwt_token')]
            ]);

            // ✅ Extraer datos
            $activos = $responseActivos['count'] ?? 0;
            $inactivos = $responseInactivos['count'] ?? 0;
            $pendientes = $responsePendientes['count'] ?? 0;
            $atendidos = $responseAtendidos['count'] ?? 0;
            $archivados = $responseArchivados['count'] ?? 0;

            // Pedidos
            $cotizacion = $responseCotizacion['count'] ?? 0;
            $pagoPendiente = $responsePagoPendiente['count'] ?? 0;
            $disenoEnProceso = $responseDisenoEnProceso['count'] ?? 0;
            $disenoAprobado = $responseDisenoAprobado['count'] ?? 0;
            $tallado = $responseTallado['count'] ?? 0;
            $engaste = $responseEngaste['count'] ?? 0;
            $pulido = $responsePulido['count'] ?? 0;
            $inspeccion = $responseInspeccion['count'] ?? 0;
            $finalizado = $responseFinalizado['count'] ?? 0;
            $cancelado = $responseCancelado['count'] ?? 0;

            return [
                // Usuarios
                'totalUsuariosActivos' => $activos,
                'totalUsuariosInactivos' => $inactivos,
                'totalUsuarios' => $activos + $inactivos,
                
                // Mensajes/Contactos
                'totalContactosPendientes' => $pendientes,
                'totalContactosAtendidos' => $atendidos,
                'totalContactosArchivados' => $archivados,
                'totalContactos' => $pendientes + $atendidos + $archivados,
                
                // Pedidos por Estado
                'pedidosCotizacionPendiente' => $cotizacion,
                'pedidosPagoDisenoPendiente' => $pagoPendiente,
                'pedidosDisenoEnProceso' => $disenoEnProceso,
                'pedidosDisenoAprobado' => $disenoAprobado,
                'pedidosEnTallado' => $tallado,
                'pedidosEnEngaste' => $engaste,
                'pedidosEnPulido' => $pulido,
                'pedidosInspeccionCalidad' => $inspeccion,
                'pedidosFinalizados' => $finalizado,
                'pedidosCancelados' => $cancelado,
                
                // Totales calculados
                'totalPedidosActivos' => $cotizacion + $pagoPendiente + $disenoEnProceso + $disenoAprobado + $tallado + $engaste + $pulido + $inspeccion,
                'totalPedidos' => $cotizacion + $pagoPendiente + $disenoEnProceso + $disenoAprobado + $tallado + $engaste + $pulido + $inspeccion + $finalizado + $cancelado,
            ];

        } catch (\Exception $e) {
            Log::error('DashboardService: Error obteniendo admin stats', [
                'error' => $e->getMessage()
            ]);
            
            return $this->getDefaultAdminStats();
        }
    }

    /**
     * Obtiene estadísticas del dashboard de diseñador
     * 
     * @return array
     */
    public function getDesignerStats(): array
    {
        try {
            // TODO: Implementar cuando tengas los endpoints específicos del diseñador
            Log::warning('DashboardService: getDesignerStats no implementado aún');
            return $this->getDefaultDesignerStats();

        } catch (\Exception $e) {
            Log::error('DashboardService: Error obteniendo designer stats', [
                'error' => $e->getMessage()
            ]);
            
            return $this->getDefaultDesignerStats();
        }
    }

    /**
     * Obtiene estadísticas del dashboard de usuario
     * 
     * @return array
     */
    public function getUserStats(): array
    {
        try {
            // TODO: Implementar cuando tengas los endpoints específicos del usuario
            Log::warning('DashboardService: getUserStats no implementado aún');
            return $this->getDefaultUserStats();

        } catch (\Exception $e) {
            Log::error('DashboardService: Error obteniendo user stats', [
                'error' => $e->getMessage()
            ]);
            
            return $this->getDefaultUserStats();
        }
    }

    /**
     * Valores por defecto para admin (cuando el API falla)
     * 
     * @return array
     */
    private function getDefaultAdminStats(): array
    {
        return [
            // Usuarios
            'totalUsuariosActivos' => 0,
            'totalUsuariosInactivos' => 0,
            'totalUsuarios' => 0,
            
            // Contactos
            'totalContactosPendientes' => 0,
            'totalContactosAtendidos' => 0,
            'totalContactosArchivados' => 0,
            'totalContactos' => 0,
            
            // Pedidos por Estado
            'pedidosCotizacionPendiente' => 0,
            'pedidosPagoDisenoPendiente' => 0,
            'pedidosDisenoEnProceso' => 0,
            'pedidosDisenoAprobado' => 0,
            'pedidosEnTallado' => 0,
            'pedidosEnEngaste' => 0,
            'pedidosEnPulido' => 0,
            'pedidosInspeccionCalidad' => 0,
            'pedidosFinalizados' => 0,
            'pedidosCancelados' => 0,
            
            // Totales
            'totalPedidosActivos' => 0,
            'totalPedidos' => 0,
        ];
    }

    /**
     * Valores por defecto para diseñador (cuando el API falla)
     * 
     * @return array
     */
    private function getDefaultDesignerStats(): array
    {
        return [
            'disenosActivos' => 0,
            'rendersPendientes' => 0,
            'comunicacionesPendientes' => 0,
            'pedidosAsignados' => 0
        ];
    }

    /**
     * Valores por defecto para usuario (cuando el API falla)
     * 
     * @return array
     */
    private function getDefaultUserStats(): array
    {
        return [
            'misPedidosActivos' => 0,
            'misPersonalizaciones' => 0,
            'pedidosCompletados' => 0
        ];
    }
}