<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

/*
 MÓDULO:Formularios de Contacto
 CAPA:Controller (Frontend Laravel)
 TIPO:Prueba de Funcionalidad (Feature Test)
 HERRAMIENTA: PHPUnit + Laravel Http::fake().
 */
class ContactoControllerTest extends TestCase
{
    
    private string $apiBase = 'http://127.0.0.1:8000/contactos';

    // Datos reutilizables
    private array $datosValidos = [
        'nombre'   => 'Juan Moreno',
        'correo'    => 'juan@gmail.com',
        'telefono' => '1255445167890',
        'mensaje'  => 'Hola, necesito información sobre sus servicios y personalizaciones.',
        'terminos' => 'true'
    ];


    /**
     * PRUEBA: Crear contacto con datos válidos
     * -----------------------------------------
     * RUTA:             POST /contacto 
     * RESULTADO ESPERADO: Redirige o retorna 200/201 con éxito
     * CASO QUE CUBRE: Happy path — todos los campos correctos
     */
    public function test_crear_contacto_con_datos_validos(): void
    {
        
        Http::fake([
            '*' => Http::response([
                'id'       => 1,
                'nombre'   => 'Juan Pérez',
                'correo'    => 'juan@email.com',
                'telefono' => '1234567890',
                'terminos' => 'true',
                'mensaje'  => 'Hola, necesito información sobre sus servicios.',
            ], 201),
        ]);

     
        $response = $this->post('/contacto', $this->datosValidos);

       
        $response->assertStatus(302);
                 

        
    Http::assertSent(function ($request) {
        return str_contains($request->url(), 'contacto')
            && $request->method() === 'POST';
        });
    }

    /**
     * PRUEBA: Crear contacto con correo inválido
     * RUTA: POST /contacto
     * RESULTADO ESPERADO: Retorna errores de validación, no llama a la API
     * CASO QUE CUBRE:  Email con formato incorrecto
     */
    public function test_crear_contacto_email_invalido(): void
    {
        
        Http::fake(); 

        $datosMalos = $this->datosValidos;
        $datosMalos['correo'] = 'esto-no-es-correo';

        
        $response = $this->post('/contacto', $datosMalos);

       
        $response->assertSessionHasErrors(['correo']);

      
        Http::assertNothingSent();
    }

    /**
     * PRUEBA: Crear contacto con campos obligatorios vacíos
     * RUTA:POST /contacto
     * RESULTADO ESPERADO: Errores de validación en todos los campos
     * CASO QUE CUBRE: Formulario completamente vacío
     */
    public function test_crear_contacto_campos_vacios(): void
    {
        Http::fake();

        
        $response = $this->post('/contacto', []);

        
        $response->assertSessionHasErrors(['nombre', 'correo', 'telefono', 'mensaje', 'terminos']);
        Http::assertNothingSent();
    }

    /**
     * PRUEBA: Crear contacto cuando la API de Spring Boot falla
     * RUTA:POST /contacto
     * RESULTADO ESPERADO: Laravel maneja el error
     * CASO QUE CUBRE: el backend está caído o da 500
     */
    public function test_crear_contacto_api_falla(): void
    {
   
        Http::fake([
            $this->apiBase => Http::response(['error' => 'Server Error'], 500),
        ]);

        $response = $this->post('/contacto', $this->datosValidos);

     
    $response->assertStatus(302);
    }


}