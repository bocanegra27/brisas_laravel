<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\Auth\AuthController;
use App\Http\Controllers\Auth\RegisterController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\Admin\UsuariosController;
use App\Http\Controllers\Admin\MensajesController;
use App\Http\Controllers\Pedido\PedidoController; 
use App\Models\Pedido; 

// ============================================
// RUTA PÚBLICA - HOME
// ============================================
Route::get('/', [HomeController::class, 'index'])->name('home');

// ============================================
// RUTA DE PRUEBA DB (DESARROLLO - ELIMINAR EN PRODUCCIÓN)
// ============================================
Route::get('/prueba-db', function () {
    try {
        $pedido = Pedido::with('estado')->first();
        
        if (!$pedido) {
            return "✅ CONEXIÓN EXITOSA: Laravel conectado a 'brisas_gems', tabla 'pedido' vacía.";
        }
        
        return [
            'status' => 'EXITO',
            'mensaje' => 'Laravel leyó tu base de datos antigua correctamente',
            'datos_pedido' => [
                'id_interno' => $pedido->ped_id,
                'codigo' => $pedido->ped_codigo,
                'comentarios' => $pedido->ped_comentarios,
                'fecha_creacion' => $pedido->ped_fecha_creacion,
                'estado_actual' => $pedido->estado?->est_nombre ?? 'Sin estado asignado'
            ]
        ];
    } catch (\Exception $e) {
        return "❌ ERROR CRÍTICO DE CONEXIÓN: " . $e->getMessage();
    }
});

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
    });
    
    // MÓDULO: PEDIDOS
    Route::controller(PedidoController::class)->prefix('pedidos')->group(function () {
        Route::get('/', 'index')->name('admin.pedidos.index');
        Route::post('/', 'store')->name('admin.pedidos.store');
        Route::put('/{id}', 'update')->name('admin.pedidos.update');
        Route::delete('/{id}', 'destroy')->name('admin.pedidos.destroy');
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
    
    // TODO: Agregar módulos específicos del usuario aquí
    
});