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

            // TODO: Cuando tengas endpoints de pedidos, agregar aquí
            // $responsePedidosDiseño = $this->apiService->get('/pedidos/count?estado=diseño', [...]);
            // etc.

            // ✅ Extraer datos
            $activos = $responseActivos['count'] ?? 0;
            $inactivos = $responseInactivos['count'] ?? 0;
            $pendientes = $responsePendientes['count'] ?? 0;
            $atendidos = $responseAtendidos['count'] ?? 0;
            $archivados = $responseArchivados['count'] ?? 0;

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
                
                // Pedidos (placeholder hasta que tengas los endpoints)
                'pedidosEnDiseño' => 0,
                'pedidosEnTallado' => 0,
                'pedidosEnEngaste' => 0,
                'pedidosEnPulido' => 0,
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
            // Por ahora retornar valores por defecto
            
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
            // Por ahora retornar valores por defecto
            
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
            
            // Pedidos
            'pedidosEnDiseño' => 0,
            'pedidosEnTallado' => 0,
            'pedidosEnEngaste' => 0,
            'pedidosEnPulido' => 0,
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