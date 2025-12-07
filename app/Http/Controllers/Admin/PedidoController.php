<?php

namespace App\Http\Controllers\Pedido;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;

class PedidoController extends Controller
{
    // Asegúrate que esta URL apunte a tu API Java
    protected $apiUrl = 'http://localhost:8080/api/pedidos';

    public function index()
    {
        try {
            // 1. Obtener la lista de pedidos existentes (Lógica original)
            $responsePedidos = Http::withHeaders([
                'Cache-Control' => 'no-cache, no-store, must-revalidate',
                'Pragma' => 'no-cache',
                'Expires' => '0'
            ])->get($this->apiUrl);
            
            $pedidos = $responsePedidos->successful() ? json_decode($responsePedidos->body()) : [];

            // 2. NUEVO: Obtener DATOS PARA EL FORMULARIO (Clientes y Opciones)
            // Llama al nuevo endpoint Java que creamos
            $responseForm = Http::get("{$this->apiUrl}/formulario-data");
            
            // Si falla, enviamos estructura vacía para evitar errores en la vista
            $datosFormulario = $responseForm->successful() ? json_decode($responseForm->body()) : (object)['clientes' => [], 'opciones' => []];

        } catch (\Exception $e) {
            $pedidos = [];
            $datosFormulario = (object)['clientes' => [], 'opciones' => []];
            \Log::error('Error al conectar con Java: ' . $e->getMessage());
        }

        return view('pedidos.index', compact('pedidos', 'datosFormulario'));
    }

    public function store(Request $request)
    {
        // 1. Validación para el nuevo formulario inteligente
        $request->validate([
            'clienteId' => 'required|integer',
            'render' => 'nullable|file|max:50000' // Soporte para imágenes o 3D
        ]);

        // 2. Preparar datos básicos para el DTO Java "PedidoCompletoRequestDTO"
        $data = [
            'pedComentarios' => $request->pedComentarios,
            'clienteId'      => $request->clienteId,
        ];

        // 3. Configurar petición Multipart
        $http = Http::asMultipart();

        // Adjuntar archivo si existe
        if ($request->hasFile('render')) {
            $renderFile = $request->file('render');
            $http->attach(
                'render', 
                file_get_contents($renderFile->getRealPath()), 
                $renderFile->getClientOriginalName()
            );
        }

        // Adjuntar campos de texto
        foreach ($data as $key => $value) {
            $http->attach($key, $value);
        }

        // CRÍTICO: Enviar array de opciones de personalización
        // Java espera "valoresPersonalizacionIds" repetido para crear la lista
        if ($request->has('valoresPersonalizacion') && is_array($request->valoresPersonalizacion)) {
            foreach ($request->valoresPersonalizacion as $valId) {
                $http->attach('valoresPersonalizacionIds', $valId);
            }
        }

        // 4. Enviar al NUEVO ENDPOINT '/completo'
        $response = $http->post("{$this->apiUrl}/completo");

        if ($response->successful()) {
            return redirect()->route('pedidos.index')
                             ->with('success', 'Pedido inteligente creado exitosamente.');
        } else {
            return back()->with('error', 'Error al crear pedido. Código Java: ' . $response->status());
        }
    }

    public function update(Request $request, $id)
    {
        // 1. Validación (Evita error 422 con GLB)
        $request->validate([
            'estId' => 'required|integer',
            'render' => 'nullable|file|max:50000'
        ]);

        // 2. Preparar datos
        $data = [
            'estId' => $request->estId,
        ];

        $targetUrl = "{$this->apiUrl}/{$id}";

        // 3. Lógica de envío condicional (Method Spoofing para Java)
        if ($request->hasFile('render')) {
            // CASO 1: CON ARCHIVO (Multipart/POST + _method=PUT)
            $renderFile = $request->file('render');
            
            $http = Http::asMultipart();
            $http->attach('render', file_get_contents($renderFile->getRealPath()), $renderFile->getClientOriginalName());
            
            $data['_method'] = 'PUT'; 
            $response = $http->post($targetUrl, $data);
        } else {
            // CASO 2: SIN ARCHIVO (Form Url Encoded/PUT standard)
            $response = Http::asForm()->put($targetUrl, $data);
        }

        // 4. Lógica AJAX para actualización dinámica de UI
        if ($request->ajax() || $request->wantsJson()) {
            if ($response->successful()) {
                $pedidoActualizado = json_decode($response->body());
                
                $estado = $pedidoActualizado->estId ?? $request->estId;
                $estadoInt = (int) $estado; 
                
                // Mapeo visual de estados
                $nombreEstado = match($estadoInt) {
                    1 => 'Diseño', 2 => 'Tallado', 3 => 'Engaste',
                    4 => 'Pulido', 5 => 'Finalizado', 6 => 'Cancelado',
                    default => 'Desconocido',
                };
                
                $progreso = match($estadoInt) {
                    1 => 15, 2 => 35, 3 => 60, 4 => 85, 5 => 100, default => 5
                };
                
                $colorEstado = match($estadoInt) {
                    1 => 'info', 2 => 'warning', 3 => 'primary', 
                    4 => 'secondary', 5 => 'success', 6 => 'danger', 
                    default => 'dark'
                };

                return response()->json([
                    'success' => true,
                    'message' => 'Pedido actualizado correctamente. (Recarga para ver el nuevo Render)',
                    'data' => [
                        'pedId' => $id,
                        'estId' => $estadoInt,
                        'nombreEstado' => $nombreEstado,
                        'progreso' => $progreso,
                        'colorEstado' => $colorEstado,
                    ]
                ]);
            } else {
                return response()->json([
                    'success' => false,
                    'message' => 'Error API: ' . $response->status()
                ], 400);
            }
        }

        // 5. Respuesta tradicional
        if ($response->successful()) {
            return redirect()->route('pedidos.index')->with('success', 'Pedido actualizado correctamente.');
        } else {
            return back()->with('error', 'No se pudo actualizar. Código: ' . $response->status());
        }
    }

    public function destroy($id)
    {
        $response = Http::delete("{$this->apiUrl}/{$id}");
        if ($response->successful()) {
            return redirect()->route('pedidos.index')->with('success', 'Pedido eliminado correctamente.');
        } else {
            return back()->with('error', 'Error al eliminar. Código: ' . $response->status());
        }
    }
}