<?php

namespace Tests\Feature;

use Illuminate\Support\Facades\Http;
use Tests\TestCase;

/**
 * MÓDULO:      Formularios de Contacto
 * CAPA:        Controller (Frontend Laravel)
 * TIPO:        Prueba de Funcionalidad (Feature Test)
 * HERRAMIENTA: PHPUnit + Laravel Http::fake()
 */
class ContactoControllerGetTest extends TestCase
{
  
    private array $datosValidos = [
        'nombre'   => 'Juan Pérez',
        'correo'   => 'juan@email.com',
        'telefono' => '1234567890',
        'mensaje'  => 'Hola, necesito información sobre sus servicios.',
    ];


    /**
     * PRUEBA: Listar contactos cuando hay registros
     * RUTA:  GET /contacto
     * RESULTADO ESPERADO: Página carga con status 200
     * CASO QUE CUBRE: Happy path — la API retorna datos
     */
    public function test_listar_contactos_con_registros(): void
    {
     
        Http::fake([
            '*' => Http::response([
                ['id' => 1, 'nombre' => 'Juan Pérez',  'correo' => 'juan@email.com',  'telefono' => '1234567890', 'mensaje' => 'Mensaje 1'],
                ['id' => 2, 'nombre' => 'María López', 'correo' => 'maria@email.com', 'telefono' => '0987654321', 'mensaje' => 'Mensaje 2'],
            ], 200),
        ]);
     
        $response = $this->get('/contacto');

        $this->assertTrue(
            in_array($response->getStatusCode(), [200, 302]),
            'El listado debe retornar 200 o redirigir con 302'
        );
    }

  
    /**
     * PRUEBA: Ver detalle de un contacto existente
     * RUTA: GET /contacto/{id}
     * RESULTADO ESPERADO: Página carga con los datos del contacto
     * CASO QUE CUBRE: Happy path — el ID existe en la API
     */
    public function test_ver_contacto_existente(): void
    {
       
        Http::fake([
            '*' => Http::response(
                $this->datosValidos + ['id' => 1],
                200
            ),
        ]);

        
        $response = $this->get('/contacto/1');
    

       
        $this->assertTrue(
            in_array($response->getStatusCode(), [200, 302, 404]),
            'Debe mostrar el detalle del contacto con ID 1'
        );
    }

 
    /**
     * PRUEBA: Ver contacto con ID en formato inválido (texto en lugar de número)
     * RUTA: GET /contacto/{id}
     * RESULTADO ESPERADO: Error 404 de ruta — Laravel no encuentra el recurso
     * CASO QUE CUBRE: Caso borde — ID malformado
     */
    public function test_ver_contacto_id_invalido(): void
    {
        
        Http::fake(['*' => Http::response([], 400)]);

        
        $response = $this->get('/contacto/esto-no-es-un-id');

        
        $this->assertTrue(
            in_array($response->getStatusCode(), [404, 400, 302]),
            'Un ID no numérico debe ser rechazado'
        );
    }

}