<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use App\Services\ApiService;
use Illuminate\Support\Facades\Session;

class UserPedidoController extends Controller
{
    protected $apiService;

    public function __construct(ApiService $apiService)
    {
        $this->apiService = $apiService;
    }

    /**
     * Muestra la lista de pedidos del usuario logueado.
     */
    public function index()
    {
        $token = Session::get('jwt_token');

        // 1. Llamar al endpoint /api/pedidos/mis-pedidos
        // Spring Boot usará el token para saber quién es el usuario.
        $pedidos = $this->apiService->get('/pedidos/mis-pedidos', [
            'headers' => ['Authorization' => 'Bearer ' . $token]
        ]);

        return view('user.pedidos.index', [
            'pedidos' => $pedidos ?? []
        ]);
    }

    /**
     * Muestra el detalle y el historial (Timeline) de un pedido específico.
     */
    public function show($id)
    {
        $token = Session::get('jwt_token');

        // 1. Obtener detalles del pedido (Validado en backend que sea mío)
        $pedido = $this->apiService->get("/pedidos/mis-pedidos/{$id}", [
            'headers' => ['Authorization' => 'Bearer ' . $token]
        ]);

        // Si el pedido es null, es porque el backend rechazó el acceso o no existe
        if (!$pedido) {
            return redirect()->route('user.pedidos.index')
                ->with('error', 'No se pudo cargar el pedido o no tienes permisos.');
        }

        // 2. Obtener el historial (Timeline)
        $historial = $this->apiService->get("/pedidos/{$id}/historial", [
            'headers' => ['Authorization' => 'Bearer ' . $token]
        ]);

        return view('user.pedidos.show', [
            'pedido' => $pedido,
            'historial' => is_array($historial) ? $historial : []
        ]);
    }
}