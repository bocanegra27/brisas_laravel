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
use App\Http\Controllers\ImagenProxyController;
use App\Http\Controllers\ContactoController;

// ============================================
// RUTAs PÚBLICAs
// ============================================
// Página de inicio
Route::get('/', [HomeController::class, 'index'])->name('home');

// Personalización de joyas
Route::get('/personalizar', [PersonalizarController::class, 'index'])->name('personalizar.index');
Route::post('/personalizar/guardar', [PersonalizarController::class, 'guardar'])->name('personalizar.guardar');
Route::get('/personalizar/{id}/detalles', [PersonalizarController::class, 'obtenerDetalles'])->name('personalizar.detalles');

// Formulario de contacto
Route::get('/contacto', [ContactoController::class, 'create'])->name('contacto.create');
Route::post('/contacto', [ContactoController::class, 'store'])->name('contacto.store');
// proxi
Route::get('/imagen/vista-anillo', [ImagenProxyController::class, 'vistaAnillo'])->name('imagen.vista-anillo');
Route::get('/imagen/icono-opcion', [ImagenProxyController::class, 'iconoOpcion'])->name('imagen.icono-opcion');



// OPCIONAL: Endpoint para limpiar caché (solo en desarrollo)
Route::get('/imagen/limpiar-cache', [ImagenProxyController::class, 'limpiarCache'])->name('imagen.limpiar-cache');


// ============================================
// AUTENTICACIÓN (INVITADOS SOLAMENTE)
// ============================================
Route::middleware('guest.custom')->group(function () {
    // Login
    Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
    Route::post('/login', [AuthController::class, 'handleLogin'])->name('login.handle');
    
    // Registro
    Route::get('/registro', [RegisterController::class, 'showRegistrationForm'])->name('register.show');
    Route::post('/registro', [RegisterController::class, 'handleRegistration'])->name('register.handle');
});

// ============================================
// LOGOUT (USUARIOS AUTENTICADOS)
// ============================================
Route::middleware('auth.custom')->group(function () {
    Route::get('/logout', [AuthController::class, 'handleLogout'])->name('logout');
});

// DASHBOARD UNIFICADO (REDIRIGE SEGÚN ROL)
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
        Route::put('/{id}', 'update')->name('admin.mensajes.update');
        Route::patch('/{id}/estado', 'cambiarEstado')->name('admin.mensajes.cambiar-estado');
        Route::delete('/{id}', 'eliminar')->name('admin.mensajes.eliminar');
        Route::get('/{id}/con-personalizacion', 'verConPersonalizacion');
    });
    
    // MÓDULO: PEDIDOS
    Route::controller(PedidoController::class)->prefix('pedidos')->group(function () {
        Route::get('/', 'index')->name('admin.pedidos.index');
        Route::post('/', 'store')->name('admin.pedidos.store');
        Route::put('/{id}', 'update')->name('admin.pedidos.update');
        Route::delete('/{id}', 'destroy')->name('admin.pedidos.destroy');
        Route::post('/desde-mensaje/{mensajeId}', 'crearDesdeMensaje');
    });
    
});

// ============================================
// ROL: DISEÑADOR
// ============================================
Route::middleware(['auth.custom', 'role:designer', 'no.back'])->prefix('designer')->group(function () {
    
    // Dashboard de diseñador
    Route::get('/dashboard', [DashboardController::class, 'designerDashboard'])->name('designer.dashboard');
    
    // por hacer: Agregar módulos específicos del diseñador aquí
    
});

// ============================================
// ROL: USUARIO (CLIENTE)
// ============================================
Route::middleware(['auth.custom', 'role:user', 'no.back'])->prefix('user')->group(function () {
    
    // Dashboard de usuario
    Route::get('/dashboard', [DashboardController::class, 'userDashboard'])->name('user.dashboard');
    
    // por hacer: Agregar módulos específicos del usuario aquí
    
});