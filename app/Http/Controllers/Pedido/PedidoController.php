<?php

namespace App\Http\Controllers\Pedido;

use App\Http\Controllers\Controller;
use App\Http\Requests\StorePedidoRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;

class PedidoController extends Controller
{
    // Asegúrate que esta URL apunte a tu API Java
    protected $apiUrl = 'http://localhost:8080/api/pedidos';

    public function index()
    {
        try {
            $response = Http::withHeaders([
                'Cache-Control' => 'no-cache, no-store, must-revalidate',
                'Pragma' => 'no-cache',
                'Expires' => '0'
            ])->get($this->apiUrl);
            
            $pedidos = $response->successful() ? json_decode($response->body()) : [];
        } catch (\Exception $e) {
            $pedidos = [];
            \Log::error('Error al obtener pedidos: ' . $e->getMessage());
        }

        return view('pedidos.index', compact('pedidos'));
    }

    public function store(StorePedidoRequest $request)
    {
        // Preparar datos básicos
        $data = [
            'pedComentarios' => $request->pedComentarios,
            'estId'          => 1,
            'perId'          => $request->perId,
            'usuId'          => $request->usuId,
        ];

        $http = Http::asMultipart();

        if ($request->hasFile('render')) {
            $renderFile = $request->file('render');
            $http->attach('render', file_get_contents($renderFile->getRealPath()), $renderFile->getClientOriginalName());
        }

        $response = $http->post($this->apiUrl, $data);

        if ($response->successful()) {
            return redirect()->route('pedidos.index')
                             ->with('success', 'Pedido creado exitosamente.');
        } else {
            return back()->with('error', 'Error al crear pedido. Código: ' . $response->status());
        }
    }

    public function update(Request $request, $id)
    {
        // 1. VALIDACIÓN (CRÍTICO: Evita el ERROR 422 en archivos GLB)
        $request->validate([
            'estId' => 'required|integer',
            'render' => 'nullable|file|max:50000' 
        ]);

        // 2. Preparar datos básicos
        $data = [
            'estId' => $request->estId,
        ];

        $targetUrl = "{$this->apiUrl}/{$id}";

        // 3. LÓGICA DE ENVÍO CONDICIONAL (CRÍTICO para la comunicación con Java)
        if ($request->hasFile('render')) {
            // CASO 1: CON ARCHIVO (Multipart/POST)
            $renderFile = $request->file('render');
            
            $http = Http::asMultipart();
            $http->attach('render', file_get_contents($renderFile->getRealPath()), $renderFile->getClientOriginalName());
            
            // Engañamos a Laravel y a Java para que lo traten como PUT
            $data['_method'] = 'PUT'; 
            $response = $http->post($targetUrl, $data);
        } else {
            // CASO 2: SIN ARCHIVO (Form Url Encoded/PUT)
            // El Java Controller ahora acepta este Content-Type.
            $response = Http::asForm()->put($targetUrl, $data);
        }

        // 4. Lógica AJAX para actualizar la barra y UI sin recargar
        if ($request->ajax() || $request->wantsJson()) {
            if ($response->successful()) {
                $pedidoActualizado = json_decode($response->body());
                
                $estado = $pedidoActualizado->estId ?? $request->estId;
                $estadoInt = (int) $estado; 
                
                // Recalcular datos visuales
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
                // Captura errores del backend Java (400, 500)
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