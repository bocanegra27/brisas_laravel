<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\Auth\AuthController;
use App\Http\Controllers\Auth\RegisterController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\Admin\UsuariosController;
use App\Http\Controllers\Admin\MensajesController;
use App\Http\Controllers\Admin\PedidoController;
use App\Http\Controllers\PersonalizarController;
use App\Http\Controllers\ImagenProxyController; // <--- Asegúrate que esto esté aquí
use App\Http\Controllers\ContactoController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\Admin\PersonalizacionAdminController;

// ============================================
// RUTAS PÚBLICAS
// ============================================

// Página de inicio
Route::get('/', [HomeController::class, 'index'])->name('home');

// Personalización de joyas
Route::controller(PersonalizarController::class)->prefix('personalizar')->group(function () {
    Route::get('/', 'index')->name('personalizar.index');
    Route::post('/guardar', 'guardar')->name('personalizar.guardar');
    Route::get('/{id}/detalles', 'obtenerDetalles')->name('personalizar.detalles');
});

// Formulario de contacto
Route::get('/contacto', [ContactoController::class, 'create'])->name('contacto.create');
Route::post('/contacto', [ContactoController::class, 'store'])->name('contacto.store');

// ============================================
// PROXY DE IMÁGENES (PUENTE A SPRING BOOT)
// ============================================
Route::controller(ImagenProxyController::class)->prefix('imagen')->group(function () {
    // Visor de anillos 3D/2D
    Route::get('/vista-anillo', 'vistaAnillo')->name('imagen.anillo');
    
    // Iconos de las opciones (materiales, formas)
    Route::get('/icono-opcion', 'iconoOpcion')->name('imagen.icono');
    
    // Utilidad para limpiar caché si las imágenes fallan
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
        
        // Manejo de estados (Patch y Post por seguridad/compatibilidad)
        Route::patch('/{id}/estado-historial', 'actualizarEstadoConHistorial')->name('admin.pedidos.actualizarEstado');
        Route::post('/{id}/estado-historial', 'actualizarEstadoConHistorial'); // Alias para formularios POST
        
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

    // MÓDULO: PERSONALIZACIÓN
    // Gestión del catálogo dinámico (Categorías -> Opciones -> Valores)
    Route::prefix('personalizacion')->group(function () {
        
        Route::controller(PersonalizacionAdminController::class)->group(function () {
            // 1. Gestión de Categorías (Anillos, Pulseras, etc.)
            Route::get('/categorias', 'indexCategorias')->name('admin.personalizacion.categorias.index');
            Route::post('/categorias', 'storeCategoria')->name('admin.personalizacion.categorias.store');
            Route::delete('/categorias/{id}','eliminarCategoria')->name('admin.personalizacion.categorias.eliminar');
            
            // 2. Gestión de Opciones (Forma, Metal, Tipo de Cierre, etc.)
            // Filtra por ?catId={id}
            Route::get('/opciones', 'indexOpciones')->name('admin.personalizacion.opciones.index');
            Route::post('/opciones', 'storeOpcion')->name('admin.personalizacion.opciones.store');
            
            // 3. Gestión de Valores e Imágenes (Oro, Plata, Cuero, etc.)
            // Filtra por ?opcId={id}
            Route::get('/valores', 'indexValores')->name('admin.personalizacion.valores.index');
            Route::post('/valores', 'storeValor')->name('admin.personalizacion.valores.store');
            Route::delete('/valores/{id}', 'eliminarValor')->name('admin.personalizacion.valores.eliminar');
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
// PERFIL DE USUARIO (COMÚN PARA TODOS)
// ============================================
Route::middleware(['auth.custom', 'no.back'])->prefix('perfil')->group(function () {
    Route::get('/', [ProfileController::class, 'index'])->name('perfil.index');
    Route::put('/actualizar', [ProfileController::class, 'update'])->name('perfil.update');
    Route::patch('/password', [ProfileController::class, 'updatePassword'])->name('perfil.password');
});