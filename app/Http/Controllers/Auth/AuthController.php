<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Services\Auth\AuthService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

/**
 * Controlador de autenticación
 * 
 * Maneja login y logout de usuarios
 */
class AuthController extends Controller
{
    private AuthService $authService;

    public function __construct(AuthService $authService)
    {
        $this->authService = $authService;
    }

    /**
     * Muestra el formulario de login
     */
    public function showLogin()
    {
        return view('auth.login');
    }

    /**
     * Procesa el intento de login
     */
    public function handleLogin(Request $request)
    {
        // Validación de datos
        $validator = Validator::make($request->all(), [
            'email' => 'required|email',
            'password' => 'required|min:8'
        ], [
            'email.required' => 'El correo es obligatorio',
            'email.email' => 'El correo debe ser válido',
            'password.required' => 'La contraseña es obligatoria',
            'password.min' => 'La contraseña debe tener al menos 8 caracteres'
        ]);

        if ($validator->fails()) {
            return back()
                ->withErrors($validator)
                ->withInput($request->only('email'));
        }

        // Intentar autenticar
        $result = $this->authService->login(
            $request->input('email'),
            $request->input('password')
        );

        // ... dentro de handleLogin, después de $result = $this->authService->login(...)

if ($result) {
    // Obtenemos el rol desde la sesión (que el AuthService ya debió guardar)
    $role = session('user_role');

    // Definimos la ruta destino según el rol
    $redirectUrl = match($role) {
        'ROLE_ADMINISTRADOR' => url('/dashboard'),
        'ROLE_DISEÑADOR'     => route('designer.pedidos.index'),
        'ROLE_USUARIO'       => route('user.pedidos.index'),
        default              => url('/'),
    };

    return redirect($redirectUrl)
        ->with('success', $result['message'] ?? 'Bienvenido');
}

        // Si falla, retornamos con error
        return back()
            ->withErrors(['login_error' => $result['message'] ?? 'Credenciales inválidas'])
            ->withInput($request->only('email'));
    }

    /**
     * Cierra sesión del usuario
     */
    public function handleLogout()
    {
        $this->authService->logout();

        return redirect('/')
            ->with('success', 'Sesión cerrada correctamente');
    }

   /**
     * Muestra el formulario de recuperación de contraseña
     */
    public function showForgotPassword()
    {
        return view('auth.forgot-password');
    }

    /**
     * Procesa la solicitud de envío de correo de recuperación
     */
    public function handleForgotPassword(Request $request)
    {
        // 1. Validar el email
        $request->validate([
            'email' => 'required|email'
        ], [
            'email.required' => 'El correo es obligatorio.',
            'email.email' => 'Ingresa un correo válido.'
        ]);

        try {
            // 2. Llamar al servicio (que se comunicará con Spring Boot)
            // Nota: Debemos asegurarnos de que el método forgotPassword exista en AuthService
            $response = $this->authService->forgotPassword($request->email);

            if ($response) {
                return back()->with('success', 'Si el correo existe, te hemos enviado instrucciones.');
            } else {
                return back()->withErrors(['email' => 'No se pudo conectar con el servidor.']);
            }
        } catch (\Exception $e) {
            return back()->withErrors(['email' => 'Ocurrió un error inesperado.']);
        }
    }

    /**
     * Muestra el formulario para ingresar la nueva contraseña
     */
    public function showResetPassword($token)
    {
        return view('auth.reset-password', ['token' => $token]);
    }

    /**
     * Procesa el cambio de contraseña
     */
    public function handleResetPassword(Request $request)
    {
        $request->validate([
            'token' => 'required',
            'password' => 'required|min:8|confirmed' // 'confirmed' verifica password_confirmation
        ]);

        // Llamar al servicio
        $success = $this->authService->resetPassword($request->token, $request->password);

        if ($success) {
            return redirect()->route('login')->with('success', '¡Contraseña cambiada! Ya puedes iniciar sesión.');
        }

        return back()->withErrors(['token' => 'El enlace es inválido o ha expirado.']);
    }
}