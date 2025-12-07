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
            // ✅ Usar los mismos endpoints que funcionan en UsuariosController
            $responseActivos = $this->apiService->get('/usuarios/count?activo=true', [
                'headers' => [
                    'Authorization' => 'Bearer ' . Session::get('jwt_token')
                ]
            ]);

            $responseInactivos = $this->apiService->get('/usuarios/count?activo=false', [
                'headers' => [
                    'Authorization' => 'Bearer ' . Session::get('jwt_token')
                ]
            ]);

            // ✅ Extraer del objeto JSON retornado por Spring Boot
            $activos = $responseActivos['count'] ?? 0;
            $inactivos = $responseInactivos['count'] ?? 0;

            return [
                'totalUsuariosActivos' => $activos,
                'totalUsuariosInactivos' => $inactivos,
                'totalUsuarios' => $activos + $inactivos,
                // TODO: Agregar otras estadísticas aquí cuando tengas los endpoints
                // 'totalPedidos' => ...,
                // 'pedidosPendientes' => ...,
                // etc.
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