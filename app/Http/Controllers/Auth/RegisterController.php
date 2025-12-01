<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Services\User\UserService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

/**
 * Controlador de registro de usuarios
 * 
 * Maneja el registro público de nuevos usuarios
 */
class RegisterController extends Controller
{
    private UserService $userService;

    public function __construct(UserService $userService)
    {
        $this->userService = $userService;
    }

    /**
     * Muestra el formulario de registro
     */
    public function showRegistrationForm()
    {
        return view('auth.register');
    }

    /**
     * Procesa el registro de un nuevo usuario
     */
    public function handleRegistration(Request $request)
    {
        // Validación de datos
        $validator = Validator::make($request->all(), [
            'nombre' => 'required|string|max:100|min:3',
            'correo' => 'required|email|max:100',
            'password' => 'required|string|min:8|max:100',
            'telefono' => 'nullable|string|max:20',
            'docnum' => 'required|string|max:20',
            'tipdocId' => 'required|integer'
        ], [
            'nombre.required' => 'El nombre es obligatorio',
            'nombre.min' => 'El nombre debe tener al menos 3 caracteres',
            'nombre.max' => 'El nombre no puede exceder 100 caracteres',
            'correo.required' => 'El correo es obligatorio',
            'correo.email' => 'El correo debe ser válido',
            'correo.max' => 'El correo no puede exceder 100 caracteres',
            'password.required' => 'La contraseña es obligatoria',
            'password.min' => 'La contraseña debe tener al menos 8 caracteres',
            'password.max' => 'La contraseña no puede exceder 100 caracteres',
            'telefono.max' => 'El teléfono no puede exceder 20 caracteres',
            'docnum.required' => 'El número de documento es obligatorio',
            'docnum.max' => 'El número de documento no puede exceder 20 caracteres',
            'tipdocId.required' => 'El tipo de documento es obligatorio',
            'tipdocId.integer' => 'El tipo de documento debe ser válido'
        ]);

        if ($validator->fails()) {
            return back()
                ->withErrors($validator)
                ->withInput($request->except('password'));
        }

        // Preparar datos para enviar al API
        $userData = [
            'nombre' => $request->input('nombre'),
            'correo' => $request->input('correo'),
            'password' => $request->input('password'),
            'telefono' => $request->input('telefono'),
            'docnum' => $request->input('docnum'),
            'tipdocId' => (int) $request->input('tipdocId'),
            'rolId' => 1, // Rol por defecto: USUARIO
            'origen' => 'registro',
            'activo' => true
        ];

        // Intentar crear usuario
        $result = $this->userService->createUser($userData);

        if ($result['success']) {
            // Registro exitoso - redirigir a login
            return redirect()->route('login')
                ->with('success', '¡Registro exitoso! Ya puedes iniciar sesión.');
        }

        // Registro fallido - mostrar error
        return back()
            ->withErrors(['correo' => $result['message']])
            ->withInput($request->except('password'));
    }
}