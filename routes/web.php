<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\Auth\AuthController;
use App\Http\Controllers\Auth\RegisterController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\Admin\UsuariosController;
use App\Http\Controllers\Admin\MensajesController;
use App\Http\Controllers\Admin\PedidoController;
use App\Http\Controllers\PersonalizarController;
use App\Http\Controllers\ImagenProxyController;
use App\Http\Controllers\ContactoController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\Admin\PersonalizacionAdminController;
use App\Http\Controllers\UserPedidoController;
use App\Http\Controllers\DesignerPedidoController;
use App\Http\Controllers\TestPedidoController;

// ============================================
// RUTAS PÚBLICAS
// ============================================

// Página de inicio
Route::get('/', [HomeController::class, 'index'])->name('home');

// Personalización de joyas
Route::controller(PersonalizarController::class)->prefix('personalizar')->group(function () {
    // 1. Ruta raíz (Mantiene la compatibilidad con el menú header)
    // Esta ruta llamará a la función index() del controlador que redirige a /personalizar/anillos
    Route::get('/', 'index')->name('personalizar.index');

    // 2. Ruta para guardar el diseño (POST)
    // Apunta a la raíz del grupo (/personalizar) con método POST
    Route::post('/', 'guardar')->name('personalizar.guardar');

    // 3. Ruta para obtener detalles (AJAX/API interna)
    Route::get('/{id}/detalles', 'obtenerDetalles')->name('personalizar.detalles');

    // 4. NUEVA RUTA DINÁMICA: Atrapa /personalizar/anillos, /personalizar/pulseras, etc.
    // IMPORTANTE: Debe ir al final del grupo para no bloquear las rutas anteriores
    Route::get('/{slug}', 'show')->name('personalizar.show');
});

// Formulario de contacto
Route::get('/contacto', [ContactoController::class, 'create'])->name('contacto.create');
Route::post('/contacto', [ContactoController::class, 'store'])->name('contacto.store');

// ============================================
// PROXY DE IMÁGENES (PUENTE A SPRING BOOT)
// ============================================
Route::controller(ImagenProxyController::class)->prefix('imagen')->group(function () {
    Route::get('/vista-anillo', 'vistaAnillo')->name('imagen.anillo');
    Route::get('/icono-opcion', 'iconoOpcion')->name('imagen.icono');
    Route::get('/limpiar-cache', 'limpiarCache')->name('imagen.limpiar-cache');
});

// ============================================
// AUTENTICACIÓN (INVITADOS SOLAMENTE)
// ============================================
Route::middleware('guest.custom')->group(function () {
    Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
    Route::post('/login', [AuthController::class, 'handleLogin'])->name('login.handle');
    Route::get('/registro', [RegisterController::class, 'showRegistrationForm'])->name('register.show');
    Route::post('/registro', [RegisterController::class, 'handleRegistration'])->name('register.handle');
    
    // Recuperación de contraseña
    Route::get('/olvide-password', [AuthController::class, 'showForgotPassword'])->name('password.request');
    Route::post('/olvide-password', [AuthController::class, 'handleForgotPassword'])->name('password.email');
    Route::get('/restablecer/{token}', [AuthController::class, 'showResetPassword'])->name('password.reset');
    Route::post('/restablecer', [AuthController::class, 'handleResetPassword'])->name('password.update');
});

// ============================================
// LOGOUT (USUARIOS AUTENTICADOS)
// ============================================
Route::middleware('auth.custom')->group(function () {
    Route::get('/logout', [AuthController::class, 'handleLogout'])->name('logout');
});

// ============================================
// DASHBOARD UNIFICADO (REDIRIGE SEGÚN ROL)
// ============================================
Route::middleware(['auth.custom', 'no.back'])->group(function () {
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');
});

// ============================================
// ROL: ADMINISTRADOR
// ============================================
Route::middleware(['auth.custom', 'role:admin', 'no.back'])->prefix('admin')->group(function () {
    
    // Dashboard principal de admin
    Route::get('/dashboard', [DashboardController::class, 'adminDashboard'])->name('admin.dashboard');
    
    // MÓDULO: USUARIOS
    Route::controller(UsuariosController::class)->prefix('usuarios')->group(function () {
        Route::get('/', 'index')->name('admin.usuarios.index');
        Route::get('/crear', 'crear')->name('admin.usuarios.crear');
        Route::post('/', 'store')->name('admin.usuarios.store');
        Route::get('/{id}/editar', 'editar')->name('admin.usuarios.editar');
        Route::put('/{id}', 'update')->name('admin.usuarios.update');
        Route::patch('/{id}/toggle-activo', 'toggleActivo')->name('admin.usuarios.toggle-activo');
        Route::delete('/{id}', 'eliminar')->name('admin.usuarios.eliminar');
    });
    
    // MÓDULO: MENSAJES/CONTACTOS
    Route::controller(MensajesController::class)->prefix('mensajes')->group(function () {
        Route::get('/', 'index')->name('admin.mensajes.index');
        Route::get('/{id}', 'ver')->name('admin.mensajes.ver');
        Route::get('/{id}/con-personalizacion', 'verConPersonalizacion')->name('admin.mensajes.ver-con-personalizacion');
        Route::put('/{id}', 'update')->name('admin.mensajes.update');
        Route::patch('/{id}/estado', 'cambiarEstado')->name('admin.mensajes.cambiar-estado');
        Route::delete('/{id}', 'eliminar')->name('admin.mensajes.eliminar');
    });
    
    // MÓDULO: PEDIDOS
    Route::controller(PedidoController::class)->prefix('pedidos')->group(function () {
        Route::get('/', 'index')->name('admin.pedidos.index');
        Route::get('/{id}/gestionar', 'gestionar')->name('admin.pedidos.gestionar');

        // Acciones específicas
        Route::post('/desde-mensaje/{mensajeId}', 'crearDesdeMensaje')->name('admin.pedidos.crear-desde-mensaje'); 
        Route::patch('/{id}/asignar-empleado', 'asignarEmpleado')->name('admin.pedidos.asignarEmpleado');
        Route::post('/{id}/subir-diseno', 'subirDiseno')->name('admin.pedidos.subir-diseno');
        
        // Manejo de estados
        Route::patch('/{id}/estado-historial', 'actualizarEstadoConHistorial')->name('admin.pedidos.actualizarEstado');
        Route::post('/{id}/estado-historial', 'actualizarEstadoConHistorial');
        
        Route::get('/{id}/historial', 'obtenerHistorial')->name('admin.pedidos.historial');
        Route::post('/{id}/subir-producto-final', 'subirProductoFinal')->name('admin.pedidos.subir-producto-final');

        // CRUD Básico
        Route::post('/', 'store')->name('admin.pedidos.store');
        Route::get('/{id}', 'show')->name('admin.pedidos.ver');
        Route::put('/{id}', 'update')->name('admin.pedidos.update');
        Route::delete('/{id}', 'destroy')->name('admin.pedidos.destroy');

        // Proxy de archivos internos (Admin)
        Route::get('/ver-archivo/{path}', 'verArchivo')
             ->where('path', '.*')
             ->name('admin.pedidos.ver-archivo');
    });

    // MÓDULO: GESTIÓN DE PERSONALIZACIÓN (CATÁLOGO)
    Route::prefix('personalizacion')->group(function () {
        Route::controller(PersonalizacionAdminController::class)->group(function () {
            // 1. Categorías
            Route::get('/categorias', 'indexCategorias')->name('admin.personalizacion.categorias.index');
            Route::post('/categorias', 'storeCategoria')->name('admin.personalizacion.categorias.store');
            Route::delete('/categorias/{id}','eliminarCategoria')->name('admin.personalizacion.categorias.eliminar');
            
            // 2. Opciones
            Route::get('/opciones', 'indexOpciones')->name('admin.personalizacion.opciones.index');
            Route::post('/opciones', 'storeOpcion')->name('admin.personalizacion.opciones.store');
            Route::delete('/opciones/{id}', 'eliminarOpcion')->name('admin.personalizacion.opciones.eliminar');
            
            // 3. Valores e Imágenes
            Route::get('/valores', 'indexValores')->name('admin.personalizacion.valores.index');
            Route::post('/valores', 'storeValor')->name('admin.personalizacion.valores.store');
            Route::delete('/valores/{id}', 'eliminarValor')->name('admin.personalizacion.valores.eliminar');
            Route::post('/valores/vistas', 'subirVista')->name('admin.personalizacion.valores.subirVista');
        });
    });
});

// ============================================
// ROL: DISEÑADOR
// ============================================
Route::middleware(['auth.custom', 'role:designer', 'no.back'])->prefix('designer')->group(function () {
    Route::get('/dashboard', [DashboardController::class, 'designerDashboard'])->name('designer.dashboard');
});

// ============================================
// ROL: USUARIO (CLIENTE)
// ============================================
Route::middleware(['auth.custom', 'role:user', 'no.back'])->prefix('user')->group(function () {
    Route::get('/dashboard', [DashboardController::class, 'userDashboard'])->name('user.dashboard');
});

// ============================================
// PERFIL DE USUARIO
// ============================================
Route::middleware(['auth.custom', 'no.back'])->prefix('perfil')->group(function () {
    Route::get('/', [ProfileController::class, 'index'])->name('perfil.index');
    Route::put('/actualizar', [ProfileController::class, 'update'])->name('perfil.update');
    Route::patch('/password', [ProfileController::class, 'updatePassword'])->name('perfil.password');
});

// Para USUARIO (cliente) - agregar después de línea 177
Route::middleware(['auth.custom', 'role:user', 'no.back'])->prefix('user')->group(function () {
    Route::get('/dashboard', [DashboardController::class, 'userDashboard'])->name('user.dashboard');
    
    // AGREGAR ESTA RUTA:
    Route::get('/pedidos', [UserPedidoController::class, 'index'])->name('user.pedidos.index');
    Route::get('/pedidos/{id}', [UserPedidoController::class, 'show'])->name('user.pedidos.show');
    Route::get('/pedidos/{id}/detalles', [UserPedidoController::class, 'detalles'])->name('user.pedidos.detalles');
});

// Para DISEÑADOR - agregar después de línea 170  
Route::middleware(['auth.custom', 'role:designer', 'no.back'])->prefix('designer')->group(function () {
    Route::get('/dashboard', [DashboardController::class, 'designerDashboard'])->name('designer.dashboard');
    
    // AGREGAR ESTA RUTA:
    Route::get('/pedidos', [DesignerPedidoController::class, 'index'])->name('designer.pedidos.index');
    Route::get('/pedidos/{id}/detalles', [DesignerPedidoController::class, 'detalles'])->name('designer.pedidos.detalles');
    Route::get('/pedidos/{id}/gestionar', [DesignerPedidoController::class, 'gestionar'])->name('designer.pedidos.gestionar');
});

// Ruta de depuración para verificar respuestas de la API
Route::get('/debug-pedido/{id}', function($id) {
    try {
        $apiService = app(\App\Services\ApiService::class);
        
        Log::info('Debug route: Consultando pedido', ['id' => $id]);
        
        // Obtener detalles del pedido
        $response = $apiService->get('/pedidos/' . $id, [
            'headers' => [
                'Authorization' => 'Bearer ' . Session::get('jwt_token')
            ]
        ]);
        
        Log::info('Debug route: Respuesta pedido', ['response' => $response]);
        
        // Obtener renders
        $renderResponse = $apiService->get('/pedidos/' . $id . '/render3d', [
            'headers' => [
                'Authorization' => 'Bearer ' . Session::get('jwt_token')
            ]
        ]);
        
        Log::info('Debug route: Respuesta renders', ['response' => $renderResponse]);
        
        // Obtener fotos
        $fotosResponse = $apiService->get('/pedidos/' . $id . '/fotos-producto-final', [
            'headers' => [
                'Authorization' => 'Bearer ' . Session::get('jwt_token')
            ]
        ]);
        
        Log::info('Debug route: Respuesta fotos', ['response' => $fotosResponse]);
        
        return response()->json([
            'pedido' => $response,
            'renders' => $renderResponse,
            'fotos' => $fotosResponse,
            'session_data' => [
                'jwt_token' => Session::get('jwt_token'),
                'user_id' => Session::get('user_id'),
                'user_role' => Session::get('user_role')
            ]
        ]);
        
    } catch (\Exception $e) {
        Log::error('Debug route: Error', ['error' => $e->getMessage()]);
        return response()->json(['error' => $e->getMessage()], 500);
    }
});

// Ruta de depuración para el proxy de imágenes
Route::get('/debug-imagen-proxy/{path}', function($path) {
    try {
        $apiService = app(\App\Services\ApiService::class);
        
        Log::info('Debug ImagenProxy: Probando proxy', ['path' => $path]);
        
        // Probar diferentes rutas de Spring Boot
        $rutas = [
            "/uploads/renders/{$path}",
            "/uploads/historial/{$path}",
            "/uploads/productos/{$path}",
            "/api/pedidos/render3d/{$path}",
            "/api/pedidos/historial/{$path}",
            "assets/uploads/{$path}"
        ];
        
        $resultados = [];
        
        foreach ($rutas as $ruta) {
            Log::info('Debug ImagenProxy: Probando ruta', ['ruta' => $ruta]);
            
            $response = $apiService->get($ruta, [
                'headers' => [
                    'Authorization' => 'Bearer ' . Session::get('jwt_token')
                ]
            ]);
            
            $resultados[] = [
                'ruta' => $ruta,
                'status' => $response ? 'success' : 'failed',
                'response' => $response ? 'Data received' : 'No response'
            ];
        }
        
        return response()->json([
            'path_original' => $path,
            'resultados' => $resultados,
            'session_data' => [
                'jwt_token' => Session::get('jwt_token'),
                'user_id' => Session::get('user_id'),
                'user_role' => Session::get('user_role')
            ]
        ]);
        
    } catch (\Exception $e) {
        Log::error('Debug ImagenProxy: Error', ['error' => $e->getMessage()]);
        return response()->json(['error' => $e->getMessage()], 500);
    }
});

// Ruta proxy para archivos (accesible para todos los roles)
Route::get('/api/archivos/proxy/{path}', function($path) {
    try {
        Log::info('Proxy archivos: Solicitando', ['path' => $path]);
        
        // Probar diferentes rutas en Spring Boot
        $rutas = [
            "http://localhost:8080/uploads/renders/{$path}",
            "http://localhost:8080/uploads/historial/{$path}",
            "http://localhost:8080/uploads/productos/{$path}",
            "http://localhost:8080/assets/uploads/{$path}",
            "http://localhost:8080/{$path}"
        ];
        
        foreach ($rutas as $ruta) {
            try {
                $response = Http::timeout(10)->get($ruta);
                
                if ($response->successful()) {
                    Log::info('Proxy archivos: Éxito', ['ruta' => $ruta, 'status' => $response->status()]);
                    
                    $contentType = $response->header('Content-Type', 'image/jpeg');
                    
                    return response($response->body())
                        ->header('Content-Type', $contentType)
                        ->header('Cache-Control', 'public, max-age=3600')
                        ->header('X-Proxy-Route', $ruta);
                }
            } catch (\Exception $e) {
                Log::warning('Proxy archivos: Error en ruta', ['ruta' => $ruta, 'error' => $e->getMessage()]);
            }
        }
        
        Log::error('Proxy archivos: No se encontró la imagen', ['path' => $path]);
        
        // Retornar placeholder
        $svg = '<svg xmlns="http://www.w3.org/2000/svg" width="400" height="300" viewBox="0 0 400 300">
            <rect width="400" height="300" fill="#f8f9fa"/>
            <text x="200" y="150" text-anchor="middle" font-family="Arial" font-size="16" fill="#6c757d">
                Imagen no encontrada
            </text>
            <text x="200" y="170" text-anchor="middle" font-family="Arial" font-size="12" fill="#adb5bd">
                Path: ' . $path . '
            </text>
        </svg>';
        
        return response($svg)
            ->header('Content-Type', 'image/svg+xml')
            ->header('Cache-Control', 'no-cache');
            
    } catch (\Exception $e) {
        Log::error('Proxy archivos: Error general', ['error' => $e->getMessage()]);
        return response('Error: ' . $e->getMessage(), 500);
    }
})->where('path', '.*');

// Ruta de depuración para ver exactamente qué devuelve la API
Route::get('/debug-api-response/{id}', function($id) {
    try {
        $apiService = app(\App\Services\ApiService::class);
        
        Log::info('Debug API Response: Consultando pedido', ['id' => $id]);
        
        // Obtener detalles del pedido
        $response = $apiService->get('/pedidos/' . $id, [
            'headers' => [
                'Authorization' => 'Bearer ' . Session::get('jwt_token')
            ]
        ]);
        
        Log::info('Debug API Response: Respuesta completa', ['response' => $response]);
        
        // Analizar específicamente los campos de imágenes
        $renderPath = $response['renderPath'] ?? null;
        $fotosFinales = $response['fotosFinales'] ?? [];
        
        return response()->json([
            'success' => true,
            'renderPath' => $renderPath,
            'renderPath_type' => gettype($renderPath),
            'fotosFinales_count' => count($fotosFinales),
            'fotosFinales' => $fotosFinales,
            'first_foto_structure' => count($fotosFinales) > 0 ? $fotosFinales[0] : null,
            'session_data' => [
                'jwt_token' => Session::get('jwt_token'),
                'user_id' => Session::get('user_id'),
                'user_role' => Session::get('user_role')
            ]
        ]);
        
    } catch (\Exception $e) {
        Log::error('Debug API Response: Error', ['error' => $e->getMessage()]);
        return response()->json([
            'success' => false,
            'error' => $e->getMessage(),
            'trace' => $e->getTraceAsString()
        ], 500);
    }
});

// Ruta de prueba para el proxy de imágenes
Route::get('/test-proxy/{path}', function($path) {
    try {
        $apiService = app(\App\Services\ApiService::class);
        
        Log::info('Test Proxy: Intentando obtener imagen', ['path' => $path]);
        
        // Probar diferentes rutas de Spring Boot
        $rutas = [
            "/uploads/renders/{$path}",
            "/uploads/historial/{$path}",
            "/uploads/productos/{$path}",
            "assets/uploads/{$path}",
            "/{$path}"
        ];
        
        foreach ($rutas as $ruta) {
            Log::info('Test Proxy: Probando ruta', ['ruta' => $ruta]);
            
            try {
                $response = Http::timeout(10)->get('http://localhost:8080' . $ruta);
                
                if ($response->successful()) {
                    Log::info('Test Proxy: Éxito', ['ruta' => $ruta, 'status' => $response->status()]);
                    return response($response->body())
                        ->header('Content-Type', $response->header('Content-Type', 'image/jpeg'))
                        ->header('X-Proxy-Route', $ruta);
                }
            } catch (\Exception $e) {
                Log::warning('Test Proxy: Error en ruta', ['ruta' => $ruta, 'error' => $e->getMessage()]);
            }
        }
        
        Log::error('Test Proxy: No se encontró la imagen en ninguna ruta');
        return response('Imagen no encontrada', 404);
        
    } catch (\Exception $e) {
        Log::error('Test Proxy: Error general', ['error' => $e->getMessage()]);
        return response('Error: ' . $e->getMessage(), 500);
    }
});

// Ruta de prueba para diagnosticar el error
Route::get('/test-pedidos', function() {
    return "Ruta de prueba funcionando - El problema está en el controlador";
});

// También agregar ruta para /mis-pedidos (alias)
Route::middleware(['auth.custom', 'role:user', 'no.back'])->get('/mis-pedidos', function() {
    return redirect()->route('user.pedidos.index');
});