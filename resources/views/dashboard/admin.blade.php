@extends('layouts.app')

@section('title', 'Panel de Administración - Brisas Gems')

@push('styles')
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;600;800&family=Playfair+Display:wght@700;900&display=swap" rel="stylesheet">

<style>
    :root {
        --primary: #009688;
        --primary-dark: #00796b;
        --primary-light: #4db6ac;
        --secondary: #ff6b6b;
        --accent: #ffd93d;
        --surface: #ffffff;
        --background: #f1f5f9;
        --text-primary: #1e293b;
        --text-secondary: #64748b;
        --shadow-sm: 0 2px 8px rgba(0,0,0,0.04);
        --shadow-md: 0 4px 16px rgba(0,0,0,0.08);
        --shadow-lg: 0 12px 32px rgba(0,0,0,0.12);
        --transition: all 0.4s cubic-bezier(0.4, 0, 0.2, 1);
        --radius: 16px;
    }

    body {
        font-family: 'Outfit', sans-serif;
        background: linear-gradient(135deg, #f1f5f9 0%, #e2e8f0 100%);
        color: var(--text-primary);
    }

    /* Navbar Moderna */
    .navbar-custom {
        background: linear-gradient(135deg, var(--primary) 0%, var(--primary-dark) 100%);
        backdrop-filter: blur(10px);
        box-shadow: var(--shadow-md);
        padding: 1rem 0;
        border-bottom: 3px solid var(--accent);
    }

    .navbar-custom .navbar-brand {
        font-family: 'Playfair Display', serif;
        font-weight: 900;
        font-size: 1.5rem;
        letter-spacing: -0.5px;
    }

    .navbar-custom .nav-link {
        font-weight: 500;
        padding: 0.5rem 1rem;
        border-radius: 8px;
        transition: var(--transition);
    }

    .navbar-custom .nav-link:hover {
        background: rgba(255,255,255,0.15);
        transform: translateY(-2px);
    }

    /* Header Section */
    .dashboard-header {
        background: linear-gradient(135deg, var(--surface) 0%, #f8fafc 100%);
        border-radius: var(--radius);
        padding: 2.5rem;
        margin-bottom: 2rem;
        box-shadow: var(--shadow-sm);
        border-left: 5px solid var(--primary);
        position: relative;
        overflow: hidden;
    }

    .dashboard-header::before {
        content: '';
        position: absolute;
        top: -50%;
        right: -10%;
        width: 300px;
        height: 300px;
        background: radial-gradient(circle, var(--primary-light) 0%, transparent 70%);
        opacity: 0.1;
        border-radius: 50%;
    }

    .dashboard-header h1 {
        font-family: 'Playfair Display', serif;
        font-weight: 900;
        font-size: 2.5rem;
        margin-bottom: 0.5rem;
        background: linear-gradient(135deg, var(--primary) 0%, var(--primary-dark) 100%);
        -webkit-background-clip: text;
        -webkit-text-fill-color: transparent;
        background-clip: text;
    }

    .role-badge {
        background: linear-gradient(135deg, var(--primary) 0%, var(--primary-dark) 100%);
        color: white;
        padding: 0.5rem 1.5rem;
        border-radius: 50px;
        font-weight: 600;
        font-size: 0.875rem;
        letter-spacing: 0.5px;
        box-shadow: var(--shadow-sm);
        display: inline-flex;
        align-items: center;
        gap: 0.5rem;
    }

    /* Section Headers */
    .section-header {
        font-family: 'Playfair Display', serif;
        font-weight: 700;
        font-size: 1.5rem;
        color: var(--text-primary);
        margin-bottom: 1.5rem;
        position: relative;
        padding-bottom: 0.75rem;
    }

    .section-header::after {
        content: '';
        position: absolute;
        bottom: 0;
        left: 0;
        width: 60px;
        height: 4px;
        background: linear-gradient(90deg, var(--primary) 0%, var(--accent) 100%);
        border-radius: 2px;
    }

    /* Stat Cards */
    .stat-card {
        text-decoration: none;
        display: block;
        height: 100%;
    }

    .stat-card .card {
        background: var(--surface);
        border: none;
        border-radius: var(--radius);
        transition: var(--transition);
        height: 100%;
        position: relative;
        overflow: hidden;
        box-shadow: var(--shadow-sm);
    }

    .stat-card .card::before {
        content: '';
        position: absolute;
        top: 0;
        left: 0;
        right: 0;
        height: 4px;
        background: linear-gradient(90deg, var(--primary) 0%, var(--primary-light) 100%);
        transform: scaleX(0);
        transform-origin: left;
        transition: transform 0.3s ease;
    }

    .stat-card:hover .card::before {
        transform: scaleX(1);
    }

    .stat-card:hover .card {
        transform: translateY(-8px);
        box-shadow: var(--shadow-lg);
    }

    .stat-card .card-body {
        padding: 2rem;
        position: relative;
        z-index: 1;
    }

    .stat-card .icon-wrapper {
        width: 70px;
        height: 70px;
        border-radius: 16px;
        display: flex;
        align-items: center;
        justify-content: center;
        margin-bottom: 1rem;
        position: relative;
        transition: var(--transition);
    }

    .stat-card:hover .icon-wrapper {
        transform: scale(1.1) rotate(5deg);
    }

    .stat-card .bi {
        font-size: 2rem;
    }

    .stat-card .card-text {
        font-size: 0.875rem;
        font-weight: 500;
        color: var(--text-secondary);
        margin-bottom: 0.5rem;
        text-transform: uppercase;
        letter-spacing: 1px;
    }

    .stat-card .display-4 {
        font-weight: 800;
        font-size: 2.5rem;
        margin: 0;
        line-height: 1;
    }

    .stat-card .trend {
        font-size: 0.75rem;
        margin-top: 0.5rem;
        display: inline-flex;
        align-items: center;
        gap: 0.25rem;
        padding: 0.25rem 0.75rem;
        border-radius: 50px;
        font-weight: 600;
    }

    .trend.up {
        background: rgba(34, 197, 94, 0.1);
        color: #16a34a;
    }

    .trend.down {
        background: rgba(239, 68, 68, 0.1);
        color: #dc2626;
    }

    /* Color Variations */
    .bg-primary-soft { background: rgba(0, 150, 136, 0.1); }
    .bg-info-soft { background: rgba(59, 130, 246, 0.1); }
    .bg-secondary-soft { background: rgba(107, 114, 128, 0.1); }
    .bg-warning-soft { background: rgba(245, 158, 11, 0.1); }
    .bg-danger-soft { background: rgba(239, 68, 68, 0.1); }
    .bg-success-soft { background: rgba(34, 197, 94, 0.1); }

    /* Animation */
    @keyframes fadeInUp {
        from {
            opacity: 0;
            transform: translateY(30px);
        }
        to {
            opacity: 1;
            transform: translateY(0);
        }
    }

    .animate-in {
        animation: fadeInUp 0.6s ease-out forwards;
    }

    .animate-delay-1 { animation-delay: 0.1s; opacity: 0; }
    .animate-delay-2 { animation-delay: 0.2s; opacity: 0; }
    .animate-delay-3 { animation-delay: 0.3s; opacity: 0; }
    .animate-delay-4 { animation-delay: 0.4s; opacity: 0; }
    .animate-delay-5 { animation-delay: 0.5s; opacity: 0; }

    /* Responsive */
    @media (max-width: 768px) {
        .dashboard-header h1 {
            font-size: 1.75rem;
        }
        .stat-card .display-4 {
            font-size: 2rem;
        }
    }
</style>
@endpush

@section('content')
<!-- Navbar -->
<nav class="navbar navbar-expand-lg navbar-dark navbar-custom mb-4">
    <div class="container">
        <a class="navbar-brand" href="{{ url('/admin/dashboard') }}">
            <i class="bi bi-gem me-2"></i>Brisas Gems
        </a>
        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
            <span class="navbar-toggler-icon"></span>
        </button>
        <div class="collapse navbar-collapse" id="navbarNav">
            <ul class="navbar-nav me-auto mb-2 mb-lg-0">
                <li class="nav-item">
                    <a class="nav-link" href="{{ url('/usuarios') }}">
                        <i class="bi bi-people me-1"></i>Usuarios
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="{{ url('/pedidos') }}">
                        <i class="bi bi-bag-check me-1"></i>Pedidos
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="{{ url('/personalizar') }}">
                        <i class="bi bi-palette2 me-1"></i>Personalizaciones
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="{{ url('/admin/contactos') }}">
                        <i class="bi bi-envelope me-1"></i>Contactos
                    </a>
                </li>
            </ul>
            <ul class="navbar-nav ms-auto">
                <li class="nav-item">
                    <a class="btn btn-outline-light" href="{{ url('/logout') }}">
                        <i class="bi bi-box-arrow-right me-1"></i>Cerrar Sesión
                    </a>
                </li>
            </ul>
        </div>
    </div>
</nav>

<main class="container mt-4 pb-5">
    <!-- Header -->
    <div class="dashboard-header animate-in">
        <div class="d-flex justify-content-between align-items-center flex-wrap gap-3">
            <div>
                <h1><i class="bi bi-speedometer2 me-2"></i>Panel de Administración</h1>
                <p class="text-muted mb-0">Bienvenido {{ Session::get('user_name', 'Administrador') }}</p>
            </div>
            <div>
                <span class="role-badge">
                    <i class="bi bi-shield-check"></i>
                    Administrador
                </span>
            </div>
        </div>
    </div>

    <!-- Estado de la Producción -->
    <h2 class="section-header animate-in animate-delay-1">Estado de la Producción</h2>
    <div class="row row-cols-1 row-cols-sm-2 row-cols-md-3 row-cols-lg-5 g-3 g-md-4 mb-5">
        <div class="col animate-in animate-delay-1">
            <a href="{{ url('/pedidos?estado=diseño') }}" class="stat-card">
                <div class="card">
                    <div class="card-body text-center">
                        <div class="icon-wrapper bg-primary-soft mx-auto">
                            <i class="bi bi-palette2 text-primary"></i>
                        </div>
                        <p class="card-text">En Diseño</p>
                        <h2 class="display-4 text-primary">{{ $data['pedidosEnDiseño'] ?? 0 }}</h2>
                        <span class="trend up">
                            <i class="bi bi-arrow-up"></i> +2 hoy
                        </span>
                    </div>
                </div>
            </a>
        </div>

        <div class="col animate-in animate-delay-2">
            <a href="{{ url('/pedidos?estado=tallado') }}" class="stat-card">
                <div class="card">
                    <div class="card-body text-center">
                        <div class="icon-wrapper bg-info-soft mx-auto">
                            <i class="bi bi-gem text-info"></i>
                        </div>
                        <p class="card-text">En Tallado</p>
                        <h2 class="display-4 text-info">{{ $data['pedidosEnTallado'] ?? 0 }}</h2>
                        <span class="trend up">
                            <i class="bi bi-arrow-up"></i> +1 hoy
                        </span>
                    </div>
                </div>
            </a>
        </div>

        <div class="col animate-in animate-delay-3">
            <a href="{{ url('/pedidos?estado=engaste') }}" class="stat-card">
                <div class="card">
                    <div class="card-body text-center">
                        <div class="icon-wrapper bg-secondary-soft mx-auto">
                            <i class="bi bi-gear text-secondary"></i>
                        </div>
                        <p class="card-text">En Engaste</p>
                        <h2 class="display-4 text-secondary">{{ $data['pedidosEnEngaste'] ?? 0 }}</h2>
                        <span class="trend">
                            <i class="bi bi-dash"></i> Sin cambios
                        </span>
                    </div>
                </div>
            </a>
        </div>

        <div class="col animate-in animate-delay-4">
            <a href="{{ url('/pedidos?estado=pulido') }}" class="stat-card">
                <div class="card">
                    <div class="card-body text-center">
                        <div class="icon-wrapper bg-warning-soft mx-auto">
                            <i class="bi bi-brightness-high text-warning"></i>
                        </div>
                        <p class="card-text">En Pulido</p>
                        <h2 class="display-4 text-warning">{{ $data['pedidosEnPulido'] ?? 0 }}</h2>
                        <span class="trend up">
                            <i class="bi bi-arrow-up"></i> +3 hoy
                        </span>
                    </div>
                </div>
            </a>
        </div>

        <div class="col animate-in animate-delay-5">
            <a href="{{ url('/pedidos?estado=cancelados') }}" class="stat-card">
                <div class="card">
                    <div class="card-body text-center">
                        <div class="icon-wrapper bg-danger-soft mx-auto">
                            <i class="bi bi-x-circle text-danger"></i>
                        </div>
                        <p class="card-text">Cancelados</p>
                        <h2 class="display-4 text-danger">{{ $data['pedidosCancelados'] ?? 0 }}</h2>
                        <span class="trend down">
                            <i class="bi bi-arrow-down"></i> -1 hoy
                        </span>
                    </div>
                </div>
            </a>
        </div>
    </div>

    <!-- Gestión General -->
    <h2 class="section-header animate-in animate-delay-2">Gestión General</h2>
    <div class="row g-3 g-md-4">
        <div class="col-lg-4 col-md-6 animate-in animate-delay-1">
            <a href="{{ url('/admin/contactos') }}" class="stat-card">
                <div class="card">
                    <div class="card-body text-center">
                        <div class="icon-wrapper bg-danger-soft mx-auto">
                            <i class="bi bi-envelope-exclamation text-danger"></i>
                        </div>
                        <p class="card-text">Mensajes Pendientes</p>
                        <h2 class="display-4 text-danger">{{ $data['totalContactosPendientes'] ?? 0 }}</h2>
                        <span class="trend up">
                            <i class="bi bi-arrow-up"></i> +5 nuevos
                        </span>
                    </div>
                </div>
            </a>
        </div>

        <div class="col-lg-4 col-md-6 animate-in animate-delay-2">
            <a href="{{ url('/usuarios') }}" class="stat-card">
                <div class="card">
                    <div class="card-body text-center">
                        <div class="icon-wrapper bg-success-soft mx-auto">
                            <i class="bi bi-people text-success"></i>
                        </div>
                        <p class="card-text">Usuarios Activos</p>
                        <h2 class="display-4 text-success">{{ $data['totalUsuariosActivos'] ?? 0 }}</h2>
                        <span class="trend up">
                            <i class="bi bi-arrow-up"></i> +8 este mes
                        </span>
                    </div>
                </div>
            </a>
        </div>

        <div class="col-lg-4 col-md-6 animate-in animate-delay-3">
            <a href="{{ url('/usuarios/inactivos') }}" class="stat-card">
                <div class="card">
                    <div class="card-body text-center">
                        <div class="icon-wrapper bg-secondary-soft mx-auto">
                            <i class="bi bi-person-slash text-secondary"></i>
                        </div>
                        <p class="card-text">Usuarios Inactivos</p>
                        <h2 class="display-4 text-secondary">{{ $data['totalUsuariosInactivos'] ?? 0 }}</h2>
                        <span class="trend">
                            <i class="bi bi-dash"></i> Sin cambios
                        </span>
                    </div>
                </div>
            </a>
        </div>
    </div>
</main>
@endsection