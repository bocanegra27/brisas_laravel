<?php

namespace App\Http\Controllers;

use App\Services\ApiService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Log;

class PersonalizarController extends Controller
{
    private ApiService $apiService;

    public function __construct(ApiService $apiService)
    {
        $this->apiService = $apiService;
    }

    /**
     * Redirección inteligente al entrar a /personalizar
     */
    public function index()
    {
        // Por defecto mandamos a anillos, o podrías hacer una landing page
        return redirect()->route('personalizar.show', ['slug' => 'anillos']);
    }

    /**
     * Muestra el diseñador para CUALQUIER categoría
     */
    public function show($slug)
        {
            try {
                // 1. Obtener TODAS las categorías (Para el menú de navegación)
                $categorias = $this->apiService->get('/categorias');
                
                // 2. Buscar la categoría actual según el slug de la URL
                $categoriaActual = collect($categorias)->firstWhere('slug', $slug);

                if (!$categoriaActual) {
                    return redirect()->route('home')->with('error', 'Categoría no encontrada.');
                }

                // 3. Obtener opciones de la categoría ACTUAL
                $opciones = $this->apiService->get("/opciones?catId={$categoriaActual['id']}");

                // 4. Llenar valores
                foreach ($opciones as &$opcion) {
                    $opcion['valores'] = $this->apiService->get("/valores?opcId={$opcion['id']}");
                }

                // 5. Enviar TODO a la vista
                return view('personalizar', [
                    'categorias' => $categorias,      // <--- ESTO ES NUEVO (La lista completa)
                    'categoria'  => $categoriaActual, // La categoría que estamos viendo
                    'opciones'   => $opciones
                ]);

            } catch (\Exception $e) {
                Log::error('Error cargando personalizador: ' . $e->getMessage());
                return redirect()->route('home')->with('error', 'Error de conexión.');
            }
        }
    /**
     * Guardar el diseño
     */
    public function guardar(Request $request)
    {
        try {
            $request->validate([
                'catId' => 'required|integer',
                'opciones' => 'required|array',
            ]);

            $data = [
                'fecha' => now()->format('Y-m-d\TH:i:s'),
                'valoresSeleccionados' => array_values($request->input('opciones'))
            ];

            if (Session::has('user_id')) {
                $data['usuarioClienteId'] = (int) Session::get('user_id');
            } elseif ($request->has('sesionId')) {
                $data['sesionId'] = (int) $request->input('sesionId');
            }

            $response = $this->apiService->post('/personalizaciones', $data);

            if ($response && isset($response['id'])) {
                return redirect()->route('contacto.create', ['personalizacionId' => $response['id']])
                    ->with('success', 'Diseño guardado correctamente.');
            }

            return back()->with('error', 'No se pudo guardar el diseño.');

        } catch (\Exception $e) {
            Log::error('Error guardando: ' . $e->getMessage());
            return back()->with('error', 'Error inesperado.');
        }
    }
}