<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Session;

/**
 * Controlador de Prueba para diagnosticar el error
 */
class TestPedidoController extends Controller
{
    /**
     * Método de prueba sin dependencias externas
     */
    public function index(Request $request)
    {
        try {
            // 1. Verificar sesión
            $userId = Session::get('user_id');
            $userRole = Session::get('user_role');
            
            if (!$userId) {
                return "No hay sesión de usuario";
            }
            
            // 2. Retornar datos de prueba
            $data = [
                'user_id' => $userId,
                'user_role' => $userRole,
                'message' => 'Controlador de prueba funcionando',
                'pedidos' => [
                    [
                        'pedCodigo' => 'TEST-001',
                        'pedFechaCreacion' => '2024-01-01',
                        'estadoNombre' => 'Test Estado',
                        'pedComentarios' => 'Pedido de prueba'
                    ]
                ]
            ];
            
            return view('user.pedidos.index', $data);
            
        } catch (\Exception $e) {
            return "Error en controlador de prueba: " . $e->getMessage();
        }
    }
}
