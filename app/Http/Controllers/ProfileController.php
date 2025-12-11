<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Services\ApiService;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Log;

class ProfileController extends Controller
{
    protected $apiService;

    public function __construct(ApiService $apiService)
    {
        $this->apiService = $apiService;
    }

    /**
     * Mostrar la vista de perfil con los datos actuales
     */
    /**
     * Mostrar la vista de perfil con los datos actuales
     */
    public function index()
    {
        try {
            $userId = Session::get('user_id'); // Asegúrate de guardar esto en el Login

            if (!$userId) {
                return redirect()->route('login')->with('error', 'Sesión no válida.');
            }

            // Obtener datos frescos del backend
            $usuario = $this->apiService->get("/usuarios/{$userId}", [
                'headers' => ['Authorization' => 'Bearer ' . Session::get('jwt_token')]
            ]);

            // 🔥 CORRECCIÓN CRÍTICA: Validar si la API devolvió datos
            // Si $usuario es null (porque no existe o falló la conexión), redirigimos para evitar el crash.
            if (!$usuario) {
                Log::warning("Perfil no encontrado para ID: {$userId}. Posible sesión obsoleta.");
                
                // Opción A: Mandar al dashboard con error
                return redirect()->route('dashboard')->with('error', 'No se pudieron cargar los datos del usuario. Es posible que el registro no exista.');
                
                // Opción B (Más agresiva pero segura): Cerrar sesión si el usuario ya no existe
                // return redirect()->route('logout'); 
            }

            return view('profile.index', compact('usuario'));

        } catch (\Exception $e) {
            Log::error('Error cargando perfil: ' . $e->getMessage());
            return back()->with('error', 'No se pudieron cargar los datos del perfil.');
        }
    }

    /**
     * Actualizar datos personales (Nombre, Teléfono, etc.)
     */
    public function update(Request $request)
    {
        $userId = Session::get('user_id');

        $request->validate([
            'nombre' => 'required|string|max:150',
            'telefono' => 'nullable|string|max:20',
            'docnum' => 'nullable|string|max:20',
            // El correo usualmente no se deja cambiar tan fácil, pero lo incluimos según tu DTO
            'correo' => 'required|email|max:100', 
        ]);

        try {
            // Datos para el DTO de Spring Boot
            $data = [
                'nombre' => $request->nombre,
                'telefono' => $request->telefono,
                'docnum' => $request->docnum,
                'correo' => $request->correo,
                // Enviamos null en lo que no queremos tocar
                'rolId' => null, 
                'activo' => true 
            ];

            $response = $this->apiService->put("/usuarios/{$userId}", $data, [
                'headers' => ['Authorization' => 'Bearer ' . Session::get('jwt_token')]
            ]);

            if ($response) {
                // Actualizar nombre en sesión para que el header cambie al instante
                Session::put('user_name', $request->nombre);
                Session::put('user_email', $request->correo);
                
                return back()->with('success', 'Perfil actualizado correctamente.');
            }

            return back()->with('error', 'No se pudo actualizar el perfil.');

        } catch (\Exception $e) {
            // Manejo de error específico si el backend devuelve 409 (Duplicado)
            if (str_contains($e->getMessage(), 'DuplicateResourceException')) {
                return back()->with('error', 'El correo o documento ya están registrados por otro usuario.');
            }
            return back()->with('error', 'Error al actualizar: ' . $e->getMessage());
        }
    }

    /**
     * Cambiar Contraseña
     */
    public function updatePassword(Request $request)
    {
        $userId = Session::get('user_id');

        $request->validate([
            'password_actual' => 'required|string',
            'password_nueva' => 'required|string|min:8|confirmed', // 'confirmed' busca password_nueva_confirmation
        ]);

        try {
            $data = [
                'passwordActual' => $request->password_actual,
                'passwordNueva' => $request->password_nueva
            ];

            // Spring Boot espera PATCH en /usuarios/{id}/password
            // 🔥 CORRECCIÓN: Capturamos la respuesta en $response
            $response = $this->apiService->patch("/usuarios/{$userId}/password", $data, [
                'headers' => ['Authorization' => 'Bearer ' . Session::get('jwt_token')]
            ]);

            // 🔥 CORRECCIÓN: Si $response es null, significa que Spring devolvió error (400 Bad Request)
            if ($response === null) {
                return back()->with('error', 'No se pudo cambiar la contraseña. Verifica tu contraseña actual.');
            }

            return back()->with('success', 'Contraseña actualizada correctamente.');

        } catch (\Exception $e) {
            // Si ocurre otro tipo de error no controlado
            return back()->with('error', 'Ocurrió un error inesperado al intentar cambiar la contraseña.');
        }
    }
}