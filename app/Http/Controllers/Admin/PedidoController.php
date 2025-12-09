<?php

namespace App\Http\Controllers\Admin;

use App\Services\ApiService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Log;

class PedidoController
{
    private ApiService $apiService;

    // Lista de estados para usar en la vista (match con Spring Boot)
    private array $estadosPosibles = [
        1 => 'Pendiente Confirmación', 2 => 'Confirmado', 3 => 'En Diseño',
        4 => 'Aprobado por Cliente', 5 => 'En Producción', 6 => 'Control de Calidad',
        7 => 'Listo para Entrega', 8 => 'En Camino', 9 => 'Entregado', 10 => 'Cancelado'
    ];

    public function __construct(ApiService $apiService)
    {
        $this->apiService = $apiService;
    }

    /**
     * 📋 Listado de pedidos
     */
public function index(Request $request)
    {
        try {
            // 1. Llamada a la API
            $response = $this->apiService->get("/pedidos", [
                'headers' => ['Authorization' => 'Bearer ' . Session::get('jwt_token')]
            ]);

            // 2. CORRECCIÓN: La API devuelve un array directo, no un objeto paginado
            // Si $response es null, usamos array vacío. Si es array, lo usamos directo.
            $listaPedidos = is_array($response) ? $response : [];

            return view('admin.pedidos.index', [
                'pedidos' => $listaPedidos,
                // Como la API actual no manda paginación, simulamos una sola página
                'pagination' => [
                    'totalElements' => count($listaPedidos),
                    'totalPages' => 1,
                    'number' => 0, 
                ],
                'estados' => $this->estadosPosibles,
                'filtroEstado' => $request->query('estadoId')
            ]);

        } catch (\Exception $e) {
            Log::error('Error listando pedidos: ' . $e->getMessage());
            return back()->with('error', 'No se pudieron cargar los pedidos.');
        }
    }

    /**
     * 👁️ Vista Robusta: Ver detalle y gestionar pedido
     */
    public function show($id)
    {
        try {
            // 1. Obtener datos del pedido
            $pedido = $this->apiService->get("/pedidos/{$id}", [
                'headers' => ['Authorization' => 'Bearer ' . Session::get('jwt_token')]
            ]);

            if (!$pedido) {
                return redirect()->route('admin.pedidos.index')->with('error', 'Pedido no encontrado.');
            }

            // 2. Intentar obtener detalles de personalización si existe referencia
            // (Asumiendo que el pedido trae datos básicos, pero queremos el detalle completo visual)
            $personalizacion = null;
            
            // Nota: Aquí dependemos de que tu backend envíe el ID de personalización dentro del pedido.
            // Si viene en $pedido['detalles'], lo usamos directamente.
            
            return view('admin.pedidos.ver', [
                'pedido' => $pedido,
                'estados' => $this->estadosPosibles
            ]);

        } catch (\Exception $e) {
            Log::error('Error viendo pedido: ' . $e->getMessage());
            return back()->with('error', 'Error de conexión.');
        }
    }

    /**
     * ✏ Actualizar estado del pedido
     */
    public function update(Request $request, $id)
    {
        $validated = $request->validate([
            'estadoId' => 'required|integer|min:1|max:10',
            'comentarios' => 'nullable|string'
        ]);

        $response = $this->apiService->put("/pedidos/{$id}", $validated, [
            'headers' => ['Authorization' => 'Bearer ' . Session::get('jwt_token')]
        ]);

        if ($response) {
            return back()->with('success', 'Estado del pedido actualizado correctamente.');
        }

        return back()->with('error', 'No se pudo actualizar el pedido.');
    }

    /**
     * 🔥 Crear pedido desde mensaje (Ya lo tenías, lo dejo igual)
     */
    public function crearDesdeMensaje(Request $request, $mensajeId)
    {
        // ... (Mantén tu código existente aquí, es correcto)
        return $this->originalCrearDesdeMensajeLogic($request, $mensajeId);
    }
    
    // Helper para mantener tu lógica anterior sin repetir código en este chat
    private function originalCrearDesdeMensajeLogic($request, $mensajeId) {
        // Pega aquí la lógica del método crearDesdeMensaje que me mostraste al principio
        // o simplemente mantén el método original en la clase.
    }
    
    // Falta implementar destroy...
    public function destroy($id) { /* Pendiente */ }
}