<?php

use Illuminate\Support\Facades\Route;
use App\Models\Pedido; // Importamos el modelo Pedido para poder usarlo en la prueba
use App\Http\Controllers\Pedido\PedidoController;

// --- RUTA ORIGINAL (Página de Bienvenida) ---
Route::get('/', function () {
    return view('welcome');
});

<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\Auth\AuthController;
use App\Http\Controllers\Auth\RegisterController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\Admin\UsuariosController;
use App\Http\Controllers\Pedido\PedidoController; // ← NUEVO
use App\Models\Pedido; // ← NUEVO (solo para ruta de prueba)

// ============================================
// RUTA PÚBLICA - HOME
// ============================================
Route::get('/', [HomeController::class, 'index'])->name('home');

// ============================================
// RUTA DE PRUEBA DB (DESARROLLO)
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
    Route::get('/dashboard', [DashboardController::class, 'adminDashboard'])->name('admin.dashboard');
    
    // MÓDULO DE USUARIOS
    Route::get('/usuarios', [UsuariosController::class, 'index'])->name('usuarios.index');
    Route::get('/usuarios/crear', [UsuariosController::class, 'crear'])->name('usuarios.crear');
    Route::post('/usuarios', [UsuariosController::class, 'store'])->name('usuarios.store');
    Route::get('/usuarios/{id}/editar', [UsuariosController::class, 'editar'])->name('usuarios.editar');
    Route::put('/usuarios/{id}', [UsuariosController::class, 'update'])->name('usuarios.update');
    Route::patch('/usuarios/{id}/toggle-activo', [UsuariosController::class, 'toggleActivo'])->name('usuarios.toggle-activo');
    Route::delete('/usuarios/{id}', [UsuariosController::class, 'eliminar'])->name('usuarios.eliminar');
    
    // ============================================
    // MÓDULO DE PEDIDOS (DE TU COMPAÑERO)
    // ============================================
    Route::get('/pedidos', [PedidoController::class, 'index'])->name('pedidos.index');
    Route::post('/pedidos', [PedidoController::class, 'store'])->name('pedidos.store');
    Route::put('/pedidos/{id}', [PedidoController::class, 'update'])->name('pedidos.update');
    Route::delete('/pedidos/{id}', [PedidoController::class, 'destroy'])->name('pedidos.destroy');
});

// DISEÑADOR
Route::middleware(['auth.custom', 'role:designer', 'no.back'])->prefix('designer')->group(function () {
    Route::get('/dashboard', [DashboardController::class, 'designerDashboard'])->name('designer.dashboard');
});

// USUARIO
Route::middleware(['auth.custom', 'role:user', 'no.back'])->prefix('user')->group(function () {
    Route::get('/dashboard', [DashboardController::class, 'userDashboard'])->name('user.dashboard');
});