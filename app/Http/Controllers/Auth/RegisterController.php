<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Services\User\UserService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use App\Services\ApiService;

class RegisterController extends Controller
{
    private UserService $userService;
    private ApiService $apiService;

    public function __construct(UserService $userService, ApiService $apiService)
    {
        $this->userService = $userService;
        $this->apiService  = $apiService;
    }

    public function showRegistrationForm()
    {
        return view('auth.register');
    }

    public function handleRegistration(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'nombre'   => 'required|string|max:100|min:3',
            'correo'   => 'required|email|max:100',
            'password' => 'required|string|min:8|max:100|confirmed',
            'telefono' => 'nullable|string|max:20',
            'docnum'   => 'required|string|max:20',
            'tipdocId' => 'required|integer'
        ], [
            'nombre.required'    => 'El nombre es obligatorio',
            'nombre.min'         => 'El nombre debe tener al menos 3 caracteres',
            'nombre.max'         => 'El nombre no puede exceder 100 caracteres',
            'correo.required'    => 'El correo es obligatorio',
            'correo.email'       => 'El correo debe ser valido',
            'correo.max'         => 'El correo no puede exceder 100 caracteres',
            'password.required'  => 'La contrasena es obligatoria',
            'password.min'       => 'La contrasena debe tener al menos 8 caracteres',
            'password.max'       => 'La contrasena no puede exceder 100 caracteres',
            'password.confirmed' => 'Las contrasenas no coinciden. Por favor verificalas.',
            'telefono.max'       => 'El telefono no puede exceder 20 caracteres',
            'docnum.required'    => 'El numero de documento es obligatorio',
            'docnum.max'         => 'El numero de documento no puede exceder 20 caracteres',
            'tipdocId.required'  => 'El tipo de documento es obligatorio',
            'tipdocId.integer'   => 'El tipo de documento debe ser valido'
        ]);

        if ($validator->fails()) {
            return back()
                ->withErrors($validator)
                ->withInput($request->except('password', 'password_confirmation'));
        }

        $userData = [
            'nombre'   => $request->input('nombre'),
            'correo'   => $request->input('correo'),
            'password' => $request->input('password'),
            'telefono' => $request->input('telefono'),
            'docnum'   => $request->input('docnum'),
            'tipdocId' => (int) $request->input('tipdocId'),
            'rolId'    => 1,
            'origen'   => 'registro',
            'activo'   => true
        ];

        $anonymousToken = $request->input('anonymousToken');

        if (!empty($anonymousToken)) {
            // Flujo con sesion anonima: crea usuario Y convierte en un solo paso
            $response = $this->apiService->post(
                "/usuarios/registro/convertir/{$anonymousToken}",
                $userData
            );

            $success = $response !== null;
            $message = $success ? null : 'El correo o documento ya esta registrado.';
        } else {
            // Flujo normal sin sesion anonima
            $result  = $this->userService->createUser($userData);
            $success = $result['success'];
            $message = $result['message'];
        }

        if ($success) {
            return redirect()->route('login')
                ->with('success', 'Registro exitoso. Ya puedes iniciar sesion.');
        }

        return back()
            ->withErrors(['correo' => $message])
            ->withInput($request->except('password', 'password_confirmation'));
    }
}