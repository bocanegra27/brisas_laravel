<?php

namespace App\Http\Controllers;

use App\Services\ApiService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;

/**
 * Controlador de Formulario de Contacto
 * 
 * Maneja el formulario público de contacto con soporte para
 * personalización vinculada y sesiones anónimas
 */
class ContactoController extends Controller
{
    private ApiService $apiService;

    public function __construct(ApiService $apiService)
    {
        $this->apiService = $apiService;
    }

    /**
     * Mostrar formulario de contacto
     * GET /contacto
     */
    public function create(Request $request)
    {
        $personalizacionId = $request->query('personalizacionId');
        $resumen = null;

        // Si viene personalizacionId, obtener detalles
        if ($personalizacionId) {
            try {
                $response = $this->apiService->get("/personalizaciones/{$personalizacionId}/detalles");
                
                if ($response !== null) {
                    $resumen = $this->construirResumen($response);
                }
            } catch (\Exception $e) {
                Log::error('ContactoController: Error al obtener personalización', [
                    'id' => $personalizacionId,
                    'error' => $e->getMessage()
                ]);
            }
        }

        return view('contacto', [
            'personalizacionId' => $personalizacionId,
            'resumen' => $resumen
        ]);
    }

    /**
     * Guardar contacto
     * POST /contacto
     */
    public function store(Request $request)
    {
        try {
            // Validación
            $validator = Validator::make($request->all(), [
                'nombre' => 'required|string|max:150',
                'correo' => 'required|email|max:100',
                'telefono' => 'required|string|max:30',
                'mensaje' => 'required|string',
                'terminos' => 'required|accepted',
                'sesionId' => 'nullable|integer',
                'personalizacionId' => 'nullable|integer'
            ], [
                'nombre.required' => 'El nombre es obligatorio',
                'correo.required' => 'El correo es obligatorio',
                'correo.email' => 'El correo debe ser válido',
                'telefono.required' => 'El teléfono es obligatorio',
                'mensaje.required' => 'El mensaje es obligatorio',
                'terminos.required' => 'Debes aceptar los términos y condiciones',
                'terminos.accepted' => 'Debes aceptar los términos y condiciones'
            ]);

            if ($validator->fails()) {
                return back()
                    ->withErrors($validator)
                    ->withInput();
            }

            // Preparar datos para el API
            $data = [
                'nombre' => $request->input('nombre'),
                'correo' => $request->input('correo'),
                'telefono' => $request->input('telefono'),
                'mensaje' => $request->input('mensaje'),
                'via' => 'formulario',
                'terminos' => true
            ];

            // Obtener los IDs y castear a INT, asegurando que sean 0 si son nulos o vacíos.
            $usuarioId = (int) session()->get('user_id');
            $sesionId = (int) $request->input('sesionId');

            // 1. PRIORIZAR USUARIO REGISTRADO
            if (session()->has('user_id') && $usuarioId > 0) { 
                // Si hay sesión y el ID es válido (> 0)
                $data['usuarioId'] = $usuarioId;
                
                // CRÍTICO: Asegurarse de que el campo 'sesionId' NO se envíe si el usuario está logueado.
                if (array_key_exists('sesionId', $data)) {
                    unset($data['sesionId']); 
                }

                Log::info('ContactoController: Usuario REGISTRADO detectado', [
                    'usuarioId' => $data['usuarioId'],
                    'nombre' => $data['nombre']
                ]);
            } 
            // 2. SINO, USAR SESIÓN ANÓNIMA
            elseif ($sesionId > 0) { 
                // Si no está logueado, pero el sesionId es válido (> 0)
                $data['sesionId'] = $sesionId;
                
                // Asegurarse de NO enviar usuarioId.
                if (array_key_exists('usuarioId', $data)) {
                    unset($data['usuarioId']); 
                }

                Log::info('ContactoController: Usuario ANÓNIMO detectado', [
                    'sesionId' => $data['sesionId'],
                    'nombre' => $data['nombre']
                ]);
            } 
            // 3. FALLBACK: EXTERNO
            else {
                Log::warning('ContactoController: Contacto EXTERNO (sin ID válido)', [
                    'nombre' => $data['nombre']
                ]);
            }

            // Agregar personalizacionId si existe
            if ($request->has('personalizacionId') && $request->input('personalizacionId')) {
                $data['personalizacionId'] = (int) $request->input('personalizacionId');
            }

            // Log de datos a enviar (para debug)
            Log::debug('ContactoController: Datos a enviar al backend', [
                'data' => $data
            ]);

            // Enviar al API
            $response = $this->apiService->post('/contactos', $data);

            if ($response === null) {
                return back()
                    ->withInput()
                    ->with('error', 'Error al enviar el mensaje. Por favor, intenta nuevamente.');
            }

            Log::info('ContactoController: Contacto creado exitosamente', [
                'id' => $response['id'],
                'tipoCliente' => $response['tipoCliente'] ?? 'desconocido',
                'usuarioId' => $response['usuarioId'] ?? null,
                'sesionId' => $response['sesionId'] ?? null
            ]);

            return redirect()->route('home')
                ->with('success', '¡Gracias por contactarnos! Te responderemos pronto.');

        } catch (\Exception $e) {
            Log::error('ContactoController@store: Excepción', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return back()
                ->withInput()
                ->with('error', 'Error al enviar el mensaje.');
        }
    }

    /**
     * Construir resumen legible de personalización
     */
    private function construirResumen(array $detalles): string
    {
        $lineas = [];
        
        foreach ($detalles as $detalle) {
            $lineas[] = "• {$detalle['valNombre']}: {$detalle['opcionNombre']}";
        }
        
        $intro = "Me interesa una joya con estas características:\n\n";
        
        return $intro . implode("\n", $lineas);
    }
}