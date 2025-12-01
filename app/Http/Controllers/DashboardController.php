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
                return $this->designerDashboard();
            case 'ROLE_USUARIO':
                return $this->userDashboard();
            default:
                return $this->userDashboard();
        }
    }

    public function adminDashboard()
    {
        $data = $this->dashboardService->getAdminStats();
        return view('dashboard.admin', compact('data'));
    }

    public function designerDashboard()
    {
        $data = $this->dashboardService->getDesignerStats();
        return view('dashboard.designer', compact('data'));
    }

    public function userDashboard()
    {
        $data = $this->dashboardService->getUserStats();
        return view('dashboard.user', compact('data'));
    }
}