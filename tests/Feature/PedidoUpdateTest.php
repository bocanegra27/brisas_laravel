<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use App\Services\Auth\AuthService;
use App\Services\ApiService;

class PedidoUpdateTest extends TestCase
{
    use RefreshDatabase;

    // Simula sesión de administrador autenticado
    private function sessionAdmin(): array
    {
        return [
            'jwt_token'  => 'fake-admin-token',
            'user_role'  => 'ROLE_ADMINISTRADOR',
            'user_name'  => 'Admin Test',
            'user_id'    => 1,
        ];
    }

    // =====================================================
    // P06 — Caja Negra
    // estadoId fuera de rango (99) debe ser rechazado
    // por la validación de Laravel sin llegar a Spring Boot
    // =====================================================
    public function test_update_pedido_con_estadoId_invalido_retorna_error_validacion(): void
    {
        $this->mock(AuthService::class, function ($mock) {
            $mock->shouldReceive('check')->andReturn(true);
        });

        $response = $this->withSession($this->sessionAdmin())
                         ->withoutMiddleware(\App\Http\Middleware\VerifyCsrfToken::class)
                         ->put('/admin/pedidos/1', [
                             'estadoId'    => 99,   // Fuera del rango max:10
                             'comentarios' => 'Comentario válido',
                         ]);

        // Laravel debe rechazar con 422 Unprocessable Entity
        $response->assertStatus(302); // back() con errores
        $response->assertSessionHasErrors(['estadoId']);
    }

    // =====================================================
    // P07 — Caja Negra
    // comentarios que excede 1000 caracteres debe fallar
    // validación antes de contactar Spring Boot
    // =====================================================
    public function test_update_pedido_con_comentarios_demasiado_largos_retorna_error_validacion(): void
    {
        $this->mock(AuthService::class, function ($mock) {
            $mock->shouldReceive('check')->andReturn(true);
        });

        $response = $this->withSession($this->sessionAdmin())
                         ->withoutMiddleware(\App\Http\Middleware\VerifyCsrfToken::class)
                         ->put('/admin/pedidos/1', [
                             'estadoId'    => 3,
                             'comentarios' => str_repeat('A', 1001), // 1001 chars — excede max:1000
                         ]);

        $response->assertStatus(302);
        $response->assertSessionHasErrors(['comentarios']);
    }

    // =====================================================
    // P08 — Funcional + Integración
    // Datos válidos pasan validación y ApiService es llamado
    // Verifica flujo completo del módulo central del negocio
    // =====================================================
    public function test_update_pedido_con_datos_validos_llama_al_api_y_redirige(): void
    {
        $this->mock(AuthService::class, function ($mock) {
            $mock->shouldReceive('check')->andReturn(true);
        });

        // Mockeamos ApiService — Spring Boot responde con el pedido actualizado
        $this->mock(ApiService::class, function ($mock) {
            $mock->shouldReceive('put')
                 ->once() // Verificamos que SÍ fue llamado exactamente una vez
                 ->andReturn([
                     'pedId'         => 1,
                     'estadoId'      => 3,
                     'comentarios'   => 'Diseño en proceso por el equipo.',
                 ]);
        });

        $response = $this->withSession($this->sessionAdmin())
                         ->withoutMiddleware(\App\Http\Middleware\VerifyCsrfToken::class)
                         ->put('/admin/pedidos/1', [
                             'estadoId'    => 3,   // Diseño en proceso — valor válido
                             'comentarios' => 'Diseño en proceso por el equipo.',
                         ]);

        // Flujo exitoso debe redirigir al listado con mensaje de éxito
        $response->assertRedirect('/admin/pedidos');
        $response->assertSessionHas('success');
    }
}

