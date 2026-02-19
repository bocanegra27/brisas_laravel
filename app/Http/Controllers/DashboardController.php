<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Session;
use App\Services\Dashboard\DashboardService;

class DashboardController extends Controller
{
    protected $dashboardService;

    public function __construct(DashboardService $dashboardService)
    {
        $this->dashboardService = $dashboardService;
    }

    public function index()
    {
        // Obtener rol del usuario
        $userRole = Session::get('user_role', 'ROLE_USUARIO');
        
        // Redirigir según el rol
        switch ($userRole) {
            case 'ROLE_ADMINISTRADOR':
                return $this->adminDashboard();
            case 'ROLE_DISEÑADOR':
                // Redirigir directamente a pedidos
                return redirect()->route('designer.pedidos.index');
            case 'ROLE_USUARIO':
                // Redirigir directamente a mis pedidos
                return redirect()->route('user.pedidos.index');
            default:
                return redirect()->route('user.pedidos.index');
        }
    }

    public function adminDashboard()
    {
        $data = $this->dashboardService->getAdminStats();
        return view('dashboard.admin', compact('data'));
    }
}