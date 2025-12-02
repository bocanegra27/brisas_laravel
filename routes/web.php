<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\Auth\AuthController;
use App\Http\Controllers\Auth\RegisterController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\Admin\UsuariosController;

// ============================================
// RUTA PÚBLICA - HOME
// ============================================
Route::get('/', [HomeController::class, 'index'])->name('home');

// ============================================
// RUTAS PÚBLICAS (solo para invitados/no logueados)
// ============================================
Route::middleware('guest.custom')->group(function () {
    Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
    Route::post('/login', [AuthController::class, 'handleLogin'])->name('login.handle');
    
    Route::get('/registro', [RegisterController::class, 'showRegistrationForm'])->name('register.show');
    Route::post('/registro', [RegisterController::class, 'handleRegistration'])->name('register.handle');
});

// ============================================
// RUTAS PROTEGIDAS (solo para usuarios autenticados)
// ============================================
Route::middleware('auth.custom')->group(function () {
    Route::get('/logout', [AuthController::class, 'handleLogout'])->name('logout');
});

// ============================================
// DASHBOARD UNIFICADO
// ============================================
Route::middleware(['auth.custom', 'no.back'])->group(function () {
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');
});

// ============================================
// RUTAS ESPECÍFICAS POR ROL
// ============================================

// ADMINISTRADOR
Route::middleware(['auth.custom', 'role:admin', 'no.back'])->prefix('admin')->group(function () {
    // Dashboard de administrador
    Route::get('/dashboard', [DashboardController::class, 'adminDashboard'])->name('admin.dashboard');
    
    // ============================================
    // MÓDULO DE USUARIOS
    // ============================================
    // Listado de usuarios con paginación y filtros
    Route::get('/usuarios', [UsuariosController::class, 'index'])->name('usuarios.index');
    
    // Formulario de creación
    Route::get('/usuarios/crear', [UsuariosController::class, 'crear'])->name('usuarios.crear');
    
    // Guardar nuevo usuario
    Route::post('/usuarios', [UsuariosController::class, 'store'])->name('usuarios.store');
    
    // Formulario de edición
    Route::get('/usuarios/{id}/editar', [UsuariosController::class, 'editar'])->name('usuarios.editar');
    
    // Actualizar usuario
    Route::put('/usuarios/{id}', [UsuariosController::class, 'update'])->name('usuarios.update');
    
    // Toggle estado (activar/desactivar) - AJAX
    Route::patch('/usuarios/{id}/toggle-activo', [UsuariosController::class, 'toggleActivo'])->name('usuarios.toggle-activo');
    
    // Eliminar usuario - AJAX
    Route::delete('/usuarios/{id}', [UsuariosController::class, 'eliminar'])->name('usuarios.eliminar');
    
    // ============================================
    // MÓDULO DE MENSAJES (Pendiente de implementar)
    // ============================================
    // Route::get('/mensajes', [MensajesController::class, 'index'])->name('mensajes.index');
    
    // ============================================
    // MÓDULO DE PEDIDOS (Pendiente de implementar)
    // ============================================
    // Route::get('/pedidos', [PedidosController::class, 'index'])->name('pedidos.index');
});

// DISEÑADOR
Route::middleware(['auth.custom', 'role:designer', 'no.back'])->prefix('designer')->group(function () {
    Route::get('/dashboard', [DashboardController::class, 'designerDashboard'])->name('designer.dashboard');
});

// USUARIO
Route::middleware(['auth.custom', 'role:user', 'no.back'])->prefix('user')->group(function () {
    Route::get('/dashboard', [DashboardController::class, 'userDashboard'])->name('user.dashboard');
});