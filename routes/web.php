<?php

use Illuminate\Support\Facades\Route;
use App\Models\Pedido; // Importamos el modelo Pedido para poder usarlo en la prueba
use App\Http\Controllers\Pedido\PedidoController;

// --- RUTA ORIGINAL (Página de Bienvenida) ---
Route::get('/', function () {
    return view('welcome');
});

// --- RUTA NUEVA (Prueba de Base de Datos) ---
Route::get('/prueba-db', function () {
    try {
        // Intentamos traer el primer pedido de la base de datos junto con su estado
        // Usamos 'with' para probar también la relación que creamos en el modelo
        $pedido = Pedido::with('estado')->first();

        // Si la consulta funciona pero no hay pedidos, avisamos
        if (!$pedido) {
            return "✅ CONEXIÓN EXITOSA: Laravel se conectó a la base de datos 'brisas_gems', pero la tabla 'pedido' está vacía.";
        }

        // Si hay un pedido, mostramos sus datos en formato JSON
        return [
            'status' => 'EXITO',
            'mensaje' => 'Laravel leyó tu base de datos antigua correctamente',
            'datos_pedido' => [
                'id_interno' => $pedido->ped_id,
                'codigo' => $pedido->ped_codigo,
                'comentarios' => $pedido->ped_comentarios,
                'fecha_creacion' => $pedido->ped_fecha_creacion,
                // Aquí probamos si la relación con EstadoPedido funciona
                'estado_actual' => $pedido->estado ? $pedido->estado->est_nombre : 'Sin estado asignado (Null)'
            ]
        ];

    } catch (\Exception $e) {
        // Si algo falla (contraseña mal, base de datos no existe, tabla mal nombrada), mostramos el error
        return "❌ ERROR CRÍTICO DE CONEXIÓN: " . $e->getMessage();
    }
});

// Grupo de rutas para Pedidos
Route::prefix('pedidos')->name('pedidos.')->group(function () {
    
    // URL: /pedidos  -> Muestra la lista
    Route::get('/', [PedidoController::class, 'index'])->name('index');
    
    // URL: /pedidos/store -> Procesa el formulario de creación
    Route::post('/store', [PedidoController::class, 'store'])->name('store');
    
    // URL: /pedidos/update/{id} -> Procesa la actualización
    Route::put('/update/{id}', [PedidoController::class, 'update'])->name('update');
    
    // URL: /pedidos/delete/{id} -> Procesa la eliminación
    Route::delete('/delete/{id}', [PedidoController::class, 'destroy'])->name('destroy');
});