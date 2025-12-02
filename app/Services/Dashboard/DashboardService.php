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
            // ✅ CORREGIDO: El endpoint NO debe incluir /api porque ApiService ya lo tiene
            $response = $this->apiService->get('/admin/dashboard/stats', [
                'headers' => [
                    'Authorization' => 'Bearer ' . Session::get('jwt_token')
                ]
            ]);

            // ✅ CORREGIDO: Manejar cuando ApiService retorna null
            if ($response === null) {
                Log::warning('DashboardService: API retornó null para admin stats');
                return $this->getDefaultAdminStats();
            }

            // Si el API retornó datos, usarlos
            return $response;

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
            // ✅ CORREGIDO: Endpoint sin /api duplicado
            $response = $this->apiService->get('/designer/dashboard/stats', [
                'headers' => [
                    'Authorization' => 'Bearer ' . Session::get('jwt_token')
                ]
            ]);

            if ($response === null) {
                Log::warning('DashboardService: API retornó null para designer stats');
                return $this->getDefaultDesignerStats();
            }

            return $response;

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
            // ✅ CORREGIDO: Endpoint sin /api duplicado
            $response = $this->apiService->get('/user/dashboard/stats', [
                'headers' => [
                    'Authorization' => 'Bearer ' . Session::get('jwt_token')
                ]
            ]);

            if ($response === null) {
                Log::warning('DashboardService: API retornó null para user stats');
                return $this->getDefaultUserStats();
            }

            return $response;

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
            'pedidosEnDiseño' => 0,
            'pedidosEnTallado' => 0,
            'pedidosEnEngaste' => 0,
            'pedidosEnPulido' => 0,
            'pedidosCancelados' => 0,
            'totalContactosPendientes' => 0,
            'totalUsuariosActivos' => 0,
            'totalUsuariosInactivos' => 0
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