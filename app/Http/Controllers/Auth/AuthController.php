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
        // Si ya está autenticado, redirigir a dashboard
        if ($this->authService->check()) {
            $user = $this->authService->user();
            return redirect($user['dashboard_url']);
        }

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

        if ($result) {
            // Login exitoso - redirigir a dashboard según rol
            return redirect($result['dashboardUrl'] ?? '/dashboard')
                ->with('success', $result['message'] ?? 'Bienvenido');
        }

        // Login fallido
        return back()
            ->withErrors(['email' => 'Correo o contraseña incorrectos'])
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
}