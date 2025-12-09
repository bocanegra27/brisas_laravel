<?php

namespace App\Http\Controllers;

use App\Services\ApiService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;

/**
 * Controlador de Personalización de Joyas
 * 
 * Maneja la interfaz de personalización y guarda las selecciones
 * Comunicación con API Spring Boot mediante ApiService
 */
class PersonalizarController extends Controller
{
    private ApiService $apiService;

    public function __construct(ApiService $apiService)
    {
        $this->apiService = $apiService;
    }

    /**
     * Mostrar pantalla de personalización
     * GET /personalizar
     */
    public function index()
    {
        try {
            // Obtener opciones de personalización (categorías)
            $opciones = $this->obtenerOpciones();

            // Obtener valores por cada opción
            $valoresPorCategoria = $this->obtenerValoresPorCategoria($opciones);

            // Preparar datos para la vista
            $data = [
                'opciones' => $opciones,
                'valores' => $valoresPorCategoria
            ];

            return view('personalizar', $data);

        } catch (\Exception $e) {
            Log::error('PersonalizarController@index: Error', [
                'error' => $e->getMessage()
            ]);

            return view('personalizar')->with([
                'opciones' => [],
                'valores' => []
            ])->with('error', 'Error al cargar las opciones de personalización.');
        }
    }

    /**
     * Guardar personalización
     * POST /personalizar/guardar
     */
    public function guardar(Request $request)
    {
        try {
            // Validación
            $validator = Validator::make($request->all(), [
                'forma' => 'required|string',
                'gema' => 'required|string',
                'material' => 'required|string',
                'tamano' => 'required|string',
                'talla' => 'required|string',
                'sesionId' => 'nullable|integer'
            ], [
                'forma.required' => 'Selecciona una forma',
                'gema.required' => 'Selecciona una gema',
                'material.required' => 'Selecciona un material',
                'tamano.required' => 'Selecciona un tamaño',
                'talla.required' => 'Selecciona una talla'
            ]);

            if ($validator->fails()) {
                return back()
                    ->withErrors($validator)
                    ->withInput();
            }

            // Obtener IDs de valores
            $valoresIds = $this->obtenerIdsDeValores([
                $request->input('forma'),
                $request->input('gema'),
                $request->input('material'),
                $request->input('tamano'),
                $request->input('talla')
            ]);

            if (empty($valoresIds)) {
                return back()
                    ->withInput()
                    ->with('error', 'Error al procesar las opciones seleccionadas.');
            }

            // Preparar datos
            $data = [
                'fecha' => now()->format('Y-m-d\TH:i:s'),
                'valoresSeleccionados' => $valoresIds
            ];

            // Agregar sesionId o usuarioClienteId
            if ($request->has('sesionId') && $request->input('sesionId')) {
                $data['sesionId'] = (int) $request->input('sesionId');
            } elseif (Session::has('user_id')) {
                $data['usuarioClienteId'] = (int) Session::get('user_id');
            }

            // Guardar en API
            $response = $this->apiService->post('/personalizaciones', $data);

            if ($response === null) {
                return back()
                    ->withInput()
                    ->with('error', 'Error al guardar la personalización.');
            }

            // Obtener ID
            $personalizacionId = $response['id'] ?? null;

            if (!$personalizacionId) {
                Log::error('PersonalizarController: No se encontró ID en respuesta', [
                    'response' => $response
                ]);
                return back()
                    ->withInput()
                    ->with('error', 'Error: No se pudo obtener el ID de la personalización.');
            }

            Log::info('Personalización guardada', [
                'id' => $personalizacionId,
                'tipo' => $response['tipoCliente'] ?? 'desconocido'
            ]);

            // Redirigir a formulario de contacto
            return redirect()->route('contacto.create', ['personalizacionId' => $personalizacionId])
                ->with('success', '¡Personalización guardada! Completa tus datos para continuar.');

        } catch (\Exception $e) {
            Log::error('Error al guardar personalización', [
                'error' => $e->getMessage()
            ]);

            return back()
                ->withInput()
                ->with('error', 'Error al guardar la personalización.');
        }
    }

    /**
     * Obtiene las opciones (categorías) del API
     */
    private function obtenerOpciones(): array
    {
        $response = $this->apiService->get('/opciones');

        if ($response === null) {
            return [];
        }

        return $response;
    }

    /**
     * Obtiene valores por cada categoría
     */
    private function obtenerValoresPorCategoria(array $opciones): array
    {
        $valores = [];

        foreach ($opciones as $opcion) {
            $opcionId = $opcion['id'];
            $response = $this->apiService->get("/valores?opcId={$opcionId}");

            if ($response !== null) {
                // Determinar clave de categoría
                $clave = $this->obtenerClaveCategoria($opcion['nombre']);
                $valores[$clave] = $response;
            }
        }

        return $valores;
    }

    /**
     * Determina la clave de categoría desde el nombre
     */
        private function obtenerClaveCategoria(string $nombre): string
    {
        $nombreLower = mb_strtolower($nombre, 'UTF-8');

        // Mapeo exacto primero
        $mapeoExacto = [
            'forma de la gema' => 'forma',
            'gema central' => 'gema',
            'material' => 'material',
            'tamaño de la gema' => 'tamano',
            'talla del anillo' => 'talla',
        ];

        if (isset($mapeoExacto[$nombreLower])) {
            return $mapeoExacto[$nombreLower];
        }

        // Fallback con contiene
        if (str_contains($nombreLower, 'forma')) {
            return 'forma';
        } elseif (str_starts_with($nombreLower, 'gema')) {
            return 'gema';
        } elseif (str_contains($nombreLower, 'material')) {
            return 'material';
        } elseif (str_contains($nombreLower, 'tamaño') || str_contains($nombreLower, 'tamano')) {
            return 'tamano';
        } elseif (str_contains($nombreLower, 'talla')) {
            return 'talla';
        }

        return 'otros';
    }

    /**
     * Convierte slugs a IDs de valores
     */
    private function obtenerIdsDeValores(array $slugs): array
    {
        $ids = [];

        // Obtener todos los valores del API
        $response = $this->apiService->get('/valores');

        if ($response === null) {
            return [];
        }

        foreach ($slugs as $slug) {
            foreach ($response as $valor) {
                $valorSlug = $this->generarSlug($valor['nombre']);
                if ($valorSlug === $slug) {
                    $ids[] = $valor['id'];
                    break;
                }
            }
        }

        return $ids;
    }

    /**
     * Genera slug desde un nombre
     */
    private function generarSlug(string $nombre): string
    {
        return strtolower(
            str_replace(
                [' ', 'á', 'é', 'í', 'ó', 'ú', 'ñ'],
                ['-', 'a', 'e', 'i', 'o', 'u', 'n'],
                $nombre
            )
        );
    }

    /**
     * Genera resumen de la personalización
     */
    private function generarResumen(Request $request): string
    {
        return sprintf(
            "Forma: %s | Gema: %s | Material: %s | Tamaño: %s | Talla: %s",
            ucfirst($request->input('forma')),
            ucfirst($request->input('gema')),
            ucfirst(str_replace('-', ' ', $request->input('material'))),
            $request->input('tamano'),
            $request->input('talla')
        );
    }
    /**
     * Obtener detalles de una personalización (para prellenar contacto)
     * GET /personalizar/{id}/detalles
     */
    public function obtenerDetalles($id)
    {
        try {
            $response = $this->apiService->get("/personalizaciones/{$id}/detalles");

            if ($response === null) {
                return response()->json([
                    'success' => false,
                    'message' => 'Personalización no encontrada'
                ], 404);
            }

            return response()->json([
                'success' => true,
                'detalles' => $response
            ]);

        } catch (\Exception $e) {
            Log::error('PersonalizarController@obtenerDetalles: Error', [
                'id' => $id,
                'error' => $e->getMessage()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Error al obtener detalles'
            ], 500);
        }
    }
    
}