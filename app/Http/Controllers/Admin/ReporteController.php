<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Services\ApiService;
use Illuminate\Support\Facades\Session;

class ReporteController extends Controller
{
    private ApiService $api;

    public function __construct(ApiService $api)
    {
        $this->api = $api;
    }

    public function index()
    {
        $pedidosPorEstado   = $this->api->get('/reportes/pedidos-por-estado')   ?? [];
        $pedidosPorDisenador = $this->api->get('/reportes/pedidos-por-disenador') ?? [];
        $pedidosSinRender   = $this->api->get('/reportes/pedidos-sin-render')   ?? [];
        $contactosSinConvertir = $this->api->get('/reportes/contactos-sin-convertir') ?? [];

        $contactosPendientes = $contactosSinConvertir['contactosPendientes'] ?? 0;

        $totalPedidos = collect($pedidosPorEstado)->sum('total');

        return view('admin.reportes.index', compact(
            'pedidosPorEstado',
            'pedidosPorDisenador',
            'pedidosSinRender',
            'contactosPendientes',
            'totalPedidos'
        ));
    }
}