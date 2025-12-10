<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Services\ApiService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Log;

class PedidoController
{
    private ApiService $apiService;

    public function __construct(ApiService $apiService)
    {
        $this->apiService = $apiService;
    }

    /**
     *  IMPLEMENTADO: Muestra el listado de pedidos.
     * GET /admin/pedidos
     */
    public function index()
    {
        try {
            // La llamada usa el JWT del ApiService
            $pedidos = $this->apiService->get('/pedidos') ?? [];

            // Nota: Si el backend retorna una respuesta paginada, 
            // necesitarías ajustar esta lógica para manejar 'content', 'totalPages', etc.
            
            // Si el API retorna null, mostramos la vista con un error.
            if ($pedidos === null) {
                return view('admin.pedidos.index', ['pedidos' => []])
                    ->with('error', 'Error de conexión con el sistema de pedidos.');
            }

            return view('admin.pedidos.index', compact('pedidos'));

        } catch (\Exception $e) {
            Log::error('PedidoController@index: Excepción', ['error' => $e->getMessage()]);
            return view('admin.pedidos.index', ['pedidos' => []])
                ->with('error', 'Error al cargar los pedidos. Por favor, revisa el log.');
        }
    }

    /**
     *  IMPLEMENTADO: Crea un pedido a partir de un mensaje de contacto.
     * POST admin/pedidos/desde-mensaje/{mensajeId}
     */
    public function crearDesdeMensaje(Request $request, $contactoId)
    {
        try {
            $usuarioIdAdmin = Session::get('user_id');

            if (!$usuarioIdAdmin) {
                return response()->json(['success' => false, 'message' => 'Admin no autenticado'], 401);
            }

            // 1. Obtener los datos del Request (Usando valor por defecto en input() para seguridad)
            $comentarios = $request->input('comentarios'); // Puede ser null
            $personalizacionId = $request->input('personalizacionId'); // Puede ser null
            
            // El estado por defecto debe ser 1 (Cotización Pendiente) si no se especifica.
            $estadoId = (int) $request->input('estadoId', 1); 
            
            $query = [
                'estadoId' => $estadoId,
                'comentarios' => $comentarios
            ];

            // 2. Añadir el personalizacionId solo si es un ID válido (> 0)
            // Usamos is_numeric y la comprobación explícita para evitar errores con null.
            if (is_numeric($personalizacionId) && (int)$personalizacionId > 0) {
                $query['personalizacionId'] = (int)$personalizacionId;
            }
            
            // 3. FIX SEGURO: Construir la URL con Query Params
            $endpointConQuery = "/pedidos/desde-contacto/{$contactoId}?" . http_build_query($query);

            // 4. Llamada POST (con body vacío, ya que los datos van en la query)
            $response = $this->apiService->post($endpointConQuery, []);

            if ($response === null || (isset($response['pedId']) === false)) {
                // Si el API de Spring Boot falló (4xx/5xx) o devolvió null/vacío.
                // Necesitas el log de Laravel para saber el error real del backend.
                Log::error('PedidoController: Error al crear pedido en backend', ['response' => $response, 'url' => $endpointConQuery]);
                return response()->json(['success' => false, 'message' => 'Error de conexión con el API de pedidos o respuesta inválida.'], 500);
            }
            
            return response()->json(['success' => true, 'message' => 'Pedido creado exitosamente.', 'pedido' => $response], 201);

        } catch (\Exception $e) {
            // 🛑 ESTE CATCH ES EL QUE TE DA EL ERROR 500 AHORA
            Log::error('PedidoController@crearDesdeMensaje: Excepción PHP', ['error' => $e->getMessage(), 'trace' => $e->getTraceAsString()]);
            return response()->json(['success' => false, 'message' => 'Excepción interna en Laravel: ' . $e->getMessage()], 500);
        }
    }

    /**
     * 🚧 PENDIENTE: Crea un pedido manualmente (requiere lógica Multipart/Form-data).
     * POST admin/pedidos
     */
    public function store(Request $request)
    {
        // Esta lógica es compleja debido a MultipartFile y debe ser refactorizada
        // para asegurar que el ApiService maneje correctamente el token en requests Multipart.
        Log::warning('PedidoController@store: Método pendiente de refactorizar para Multipart.');
        return back()->with('error', 'La creación manual de pedidos (store) está pendiente de implementación Multipart segura.');
    }

    /**
     * 🚧 PENDIENTE: Actualiza un pedido (requiere lógica Multipart/Form-data).
     * PUT admin/pedidos/{id}
     */
    public function update(Request $request, $id)
    {
        // Similar a store, requiere un manejo especializado de multipart y method spoofing.
        Log::warning('PedidoController@update: Método pendiente de refactorizar para Multipart.');
        return back()->with('error', 'La actualización de pedidos (update) está pendiente de implementación Multipart segura.');
    }

    /**
     * 🔥 IMPLEMENTADO: Elimina un pedido.
     * DELETE admin/pedidos/{id}
     */
    public function destroy($id)
    {
        try {
            // ApiService maneja el JWT
            $response = $this->apiService->delete("/pedidos/{$id}");
            
            if ($response !== null) {
                return redirect()->route('admin.pedidos.index')->with('success', 'Pedido eliminado correctamente.');
            } else {
                return back()->with('error', 'Error al eliminar el pedido. Podría no existir o el backend falló.');
            }
        } catch (\Exception $e) {
            Log::error('PedidoController@destroy: Excepción', ['id' => $id, 'error' => $e->getMessage()]);
            return back()->with('error', 'Error interno al intentar eliminar el pedido.');
        }
    }
    
    // Falta implementar destroy...
    public function destroy($id) { /* Pendiente */ }
}