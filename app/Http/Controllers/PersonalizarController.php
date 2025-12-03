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
                'talla' => 'required|string'
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

            // Obtener IDs de valores desde los slugs enviados
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

            // Preparar datos para el API
            $data = [
                'usuarioClienteId' => Session::has('user_id') ? Session::get('user_id') : null,
                'fecha' => date('Y-m-d'),
                'valoresSeleccionados' => $valoresIds
            ];

            // Guardar personalización en el API
            $response = $this->apiService->post('/personalizaciones', $data, [
                'headers' => Session::has('jwt_token') 
                    ? ['Authorization' => 'Bearer ' . Session::get('jwt_token')]
                    : []
            ]);

            if ($response === null) {
                Log::warning('PersonalizarController@guardar: API retornó null');
                return back()
                    ->withInput()
                    ->with('error', 'Error al guardar la personalización.');
            }

            // Guardar ID en sesión para uso futuro (cuando tengas el módulo de contacto)
            Session::put('ultima_personalizacion_id', $response['id']);
            Session::put('ultima_personalizacion_resumen', $this->generarResumen($request));

            Log::info('PersonalizarController: Personalización guardada', [
                'id' => $response['id']
            ]);

            // Por ahora, redirigir de vuelta con éxito
            // Cuando tengas el módulo de contacto, cambia esto a: return redirect()->route('contacto.create')
            return back()->with('success', '¡Personalización guardada! Pronto podrás continuar con un asesor.');

        } catch (\Exception $e) {
            Log::error('PersonalizarController@guardar: Excepción', [
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
}