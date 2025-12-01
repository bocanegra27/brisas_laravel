<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\Auth\AuthController;
use App\Http\Controllers\Auth\RegisterController;
use App\Http\Controllers\DashboardController;

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


// Dashboard unificado (redirige según rol)
Route::middleware(['auth.custom'])->group(function () {
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');
});

// Rutas específicas por rol (opcional, para acceso directo)
Route::middleware(['auth.custom', 'role:admin'])->group(function () {
    Route::get('/admin/dashboard', [DashboardController::class, 'adminDashboard'])->name('admin.dashboard');
});

Route::middleware(['auth.custom', 'role:designer'])->group(function () {
    Route::get('/designer/dashboard', [DashboardController::class, 'designerDashboard'])->name('designer.dashboard');
});

Route::middleware(['auth.custom', 'role:user'])->group(function () {
    Route::get('/user/dashboard', [DashboardController::class, 'userDashboard'])->name('user.dashboard');
});