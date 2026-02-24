<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use App\Services\Auth\AuthService;
use App\Services\ApiService;

class MiddlewareRolTest extends TestCase
{
    use RefreshDatabase;

    // =====================================================
    // ESCENARIO 1
    // Sin sesión activa — debe redirigir a login
    // Tipo: Integración (auth.custom middleware)
    // =====================================================
    public function test_usuario_sin_sesion_no_puede_acceder_a_admin_pedidos(): void
    {
        $this->mock(AuthService::class, function ($mock) {
            // Sin sesión: check() retorna false
            $mock->shouldReceive('check')->andReturn(false);
        });

        $response = $this->get('/admin/pedidos');

        // El middleware auth.custom debe redirigir al login
        $response->assertRedirect('/login');
    }

    // =====================================================
    // ESCENARIO 2
    // Sesión activa pero rol ROLE_USUARIO — debe redirigir a su dashboard
    // Tipo: Integración (auth.custom + role:admin middleware)
    // =====================================================
    public function test_usuario_con_rol_cliente_no_puede_acceder_a_admin_pedidos(): void
    {
        $this->mock(AuthService::class, function ($mock) {
            // Está autenticado pero NO es admin
            $mock->shouldReceive('check')->andReturn(true);
        });

        // Simulamos la sesión de un cliente normal
        $response = $this->withSession([
            'jwt_token'     => 'fake-token',
            'user_role'     => 'ROLE_USUARIO',
            'user_name'     => 'Cliente Test',
            'dashboard_url' => '/user/dashboard',
        ])->get('/admin/pedidos');

        // CheckRole debe redirigir a su propio dashboard
        $response->assertRedirect('/user/pedidos');
    }

    // =====================================================
    // ESCENARIO 3
    // Sesión activa con rol ROLE_ADMINISTRADOR — debe permitir acceso
    // Tipo: Integración completa (ambos middlewares + controlador)
    // =====================================================
    public function test_usuario_con_rol_admin_puede_acceder_a_admin_pedidos(): void
    {
        $this->mock(AuthService::class, function ($mock) {
            $mock->shouldReceive('check')->andReturn(true);
        });

        // Mockeamos ApiService para que el controlador no llame a Spring Boot
        $this->mock(ApiService::class, function ($mock) {
            // El controlador llama a /pedidos, /pedidos/count y /usuarios/empleados
            $mock->shouldReceive('get')->andReturn([
                'content'       => [],
                'totalElements' => 0,
                'totalPages'    => 0,
                'pageable'      => ['pageNumber' => 0, 'pageSize' => 10]
            ]);
        });

        $response = $this->withSession([
            'jwt_token'     => 'fake-admin-token',
            'user_role'     => 'ROLE_ADMINISTRADOR',
            'user_name'     => 'Admin Test',
            'user_id'       => 1,
            'dashboard_url' => '/admin/dashboard',
        ])->get('/admin/pedidos');

        // El admin sí entra — esperamos 200
        $response->assertStatus(200);
    }
}