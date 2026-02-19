<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use App\Services\Auth\AuthService;

class LoginTest extends TestCase
{
    use RefreshDatabase;

    /**
     * P01 - Prueba Funcional: Login con credenciales válidas
     * Tipo: Funcional / Caja Negra
     * Entrada: email y password correctos
     * Salida esperada: redirección al dashboard del usuario
     */
    public function test_login_con_credenciales_validas(): void
    {
        $this->mock(AuthService::class, function ($mock) {
            // El middleware pregunta "¿ya está logueado?" → No
            $mock->shouldReceive('check')->andReturn(false);

            // El controlador llama a login() → éxito
            $mock->shouldReceive('login')->once()->andReturn([
                'token'        => 'fake-jwt-token',
                'userRole'     => 'ROLE_USUARIO',
                'userName'     => 'Test User',
                'email'        => 'test@brisasgems.com',
                'userId'       => 1,
                'dashboardUrl' => '/user/dashboard'
            ]);
        });

        $response = $this->withoutMiddleware(\App\Http\Middleware\VerifyCsrfToken::class)
                         ->post('/login', [
                             'email'    => 'test@brisasgems.com',
                             'password' => 'password123',
                         ]);

        $response->assertRedirect('/user/dashboard');
    }

    /**
     * P02 - Prueba Funcional: Login con credenciales inválidas
     * Tipo: Funcional / Caja Negra
     * Entrada: contraseña incorrecta
     * Salida esperada: regresa al login con mensaje de error
     */
    public function test_login_con_credenciales_invalidas(): void
    {
        $this->mock(AuthService::class, function ($mock) {
            // El middleware pregunta "¿ya está logueado?" → No
            $mock->shouldReceive('check')->andReturn(false);

            // Spring Boot rechaza → AuthService retorna null
            $mock->shouldReceive('login')->once()->andReturn(null);
        });

        $response = $this->withoutMiddleware(\App\Http\Middleware\VerifyCsrfToken::class)
                         ->post('/login', [
                             'email'    => 'test@brisasgems.com',
                             'password' => 'wrongpassword',
                         ]);

        $response->assertRedirect();
        $response->assertSessionHasErrors(['email']);
    }
}