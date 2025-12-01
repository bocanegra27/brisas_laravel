@extends('layouts.app')

@section('title', 'Panel del Diseñador - Brisas Gems')

@push('styles')
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=DM+Sans:wght@400;500;700&family=Fraunces:wght@700;900&display=swap" rel="stylesheet">

<style>
    :root {
        --primary: #009688;
        --primary-dark: #00695c;
        --primary-light: #4db6ac;
        --accent: #e91e63;
        --accent-light: #f48fb1;
        --surface: #ffffff;
        --background: #fafafa;
        --text-primary: #212121;
        --text-secondary: #757575;
        --shadow-sm: 0 2px 8px rgba(0,0,0,0.06);
        --shadow-md: 0 8px 24px rgba(0,0,0,0.1);
        --shadow-lg: 0 16px 40px rgba(0,0,0,0.15);
        --transition: all 0.35s cubic-bezier(0.4, 0, 0.2, 1);
        --radius: 20px;
    }

    body {
        font-family: 'DM Sans', sans-serif;
        background: #fafafa;
        background-image: 
            radial-gradient(circle at 20% 50%, rgba(0, 150, 136, 0.03) 0%, transparent 50%),
            radial-gradient(circle at 80% 80%, rgba(233, 30, 99, 0.03) 0%, transparent 50%);
        color: var(--text-primary);
    }

    /* Navbar Creativa */
    .navbar-custom {
        background: linear-gradient(135deg, #ffffff 0%, #f5f5f5 100%);
        box-shadow: 0 4px 20px rgba(0,0,0,0.08);
        padding: 1rem 0;
        border-bottom: 2px solid var(--primary);
    }

    .navbar-custom .navbar-brand {
        font-family: 'Fraunces', serif;
        font-weight: 900;
        font-size: 1.5rem;
        color: var(--primary);
        display: flex;
        align-items: center;
        gap: 0.5rem;
    }

    .navbar-custom .nav-link {
        font-weight: 500;
        color: var(--text-primary);
        padding: 0.5rem 1rem;
        border-radius: 10px;
        transition: var(--transition);
    }

    .navbar-custom .nav-link:hover {
        background: rgba(0, 150, 136, 0.1);
        color: var(--primary);
        transform: translateY(-2px);
    }

    .navbar-custom .dropdown-menu {
        border: none;
        box-shadow: var(--shadow-md);
        border-radius: 12px;
        padding: 0.5rem;
    }

    .navbar-custom .dropdown-item {
        border-radius: 8px;
        padding: 0.75rem 1rem;
        transition: var(--transition);
    }

    .navbar-custom .dropdown-item:hover {
        background: rgba(0, 150, 136, 0.1);
        color: var(--primary);
    }

    /* Header Premium */
    .designer-header {
        background: linear-gradient(135deg, var(--primary) 0%, var(--primary-dark) 100%);
        border-radius: var(--radius);
        padding: 3rem;
        margin-bottom: 2.5rem;
        box-shadow: var(--shadow-lg);
        position: relative;
        overflow: hidden;
        color: white;
    }

    .designer-header::before {
        content: '';
        position: absolute;
        top: -50%;
        right: -20%;
        width: 500px;
        height: 500px;
        background: radial-gradient(circle, rgba(255,255,255,0.2) 0%, transparent 70%);
        border-radius: 50%;
    }

    .designer-header::after {
        content: '✨';
        position: absolute;
        bottom: 2rem;
        right: 2rem;
        font-size: 4rem;
        opacity: 0.3;
    }

    .designer-header h1 {
        font-family: 'Fraunces', serif;
        font-weight: 900;
        font-size: 2.75rem;
        margin-bottom: 0.5rem;
        position: relative;
        z-index: 1;
    }

    .designer-header p {
        font-size: 1.125rem;
        opacity: 0.9;
        position: relative;
        z-index: 1;
    }

    .role-badge {
        background: rgba(255, 255, 255, 0.2);
        backdrop-filter: blur(10px);
        color: white;
        padding: 0.75rem 1.5rem;
        border-radius: 50px;
        font-weight: 600;
        font-size: 0.875rem;
        letter-spacing: 0.5px;
        display: inline-flex;
        align-items: center;
        gap: 0.5rem;
        border: 2px solid rgba(255, 255, 255, 0.3);
    }

    /* Section Headers */
    .section-header {
        font-family: 'Fraunces', serif;
        font-weight: 700;
        font-size: 1.75rem;
        color: var(--text-primary);
        margin-bottom: 1.5rem;
        display: flex;
        align-items: center;
        gap: 1rem;
    }

    .section-header::before {
        content: '';
        width: 6px;
        height: 40px;
        background: linear-gradient(180deg, var(--primary) 0%, var(--accent) 100%);
        border-radius: 3px;
    }

    /* Stat Cards Creativas */
    .stat-card {
        text-decoration: none;
        display: block;
        height: 100%;
    }

    .stat-card .card {
        background: white;
        border: none;
        border-radius: var(--radius);
        transition: var(--transition);
        height: 100%;
        position: relative;
        overflow: hidden;
        box-shadow: var(--shadow-sm);
        border: 2px solid transparent;
    }

    .stat-card:hover .card {
        transform: translateY(-10px) rotate(-1deg);
        box-shadow: var(--shadow-lg);
        border-color: var(--primary);
    }

    .stat-card .card-body {
        padding: 2rem;
        position: relative;
        z-index: 2;
    }

    .stat-card .icon-bg {
        position: absolute;
        top: -20px;
        right: -20px;
        width: 120px;
        height: 120px;
        border-radius: 50%;
        opacity: 0.08;
        z-index: 0;
    }

    .stat-card .icon-wrapper {
        width: 80px;
        height: 80px;
        border-radius: 20px;
        display: flex;
        align-items: center;
        justify-content: center;
        margin-bottom: 1.5rem;
        position: relative;
        transition: var(--transition);
        box-shadow: var(--shadow-sm);
    }

    .stat-card:hover .icon-wrapper {
        transform: scale(1.15) rotate(-10deg);
        box-shadow: var(--shadow-md);
    }

    .stat-card .bi {
        font-size: 2.25rem;
    }

    .stat-card .card-text {
        font-size: 0.875rem;
        font-weight: 600;
        color: var(--text-secondary);
        margin-bottom: 1rem;
        text-transform: uppercase;
        letter-spacing: 1.5px;
    }

    .stat-card .display-4 {
        font-weight: 900;
        font-size: 3rem;
        margin: 0;
        line-height: 1;
        font-family: 'Fraunces', serif;
    }

    /* Color Variations */
    .bg-primary-gradient { background: linear-gradient(135deg, #009688 0%, #00796b 100%); }
    .bg-info-gradient { background: linear-gradient(135deg, #00bcd4 0%, #0097a7 100%); }
    .bg-warning-gradient { background: linear-gradient(135deg, #ffc107 0%, #f57c00 100%); }
    .bg-success-gradient { background: linear-gradient(135deg, #4caf50 0%, #388e3c 100%); }

    /* Action Cards */
    .action-card {
        background: white;
        border: 2px dashed var(--primary);
        border-radius: var(--radius);
        padding: 2rem;
        transition: var(--transition);
        text-decoration: none;
        display: block;
        position: relative;
        overflow: hidden;
    }

    .action-card::before {
        content: '';
        position: absolute;
        top: 0;
        left: 0;
        right: 0;
        bottom: 0;
        background: linear-gradient(135deg, rgba(0, 150, 136, 0.05) 0%, rgba(233, 30, 99, 0.05) 100%);
        opacity: 0;
        transition: var(--transition);
    }

    .action-card:hover {
        transform: translateY(-5px);
        border-style: solid;
        box-shadow: var(--shadow-md);
    }

    .action-card:hover::before {
        opacity: 1;
    }

    .action-card h6 {
        font-weight: 700;
        color: var(--primary);
        margin-bottom: 0.5rem;
        position: relative;
        z-index: 1;
    }

    .action-card p {
        color: var(--text-secondary);
        font-size: 0.875rem;
        margin-bottom: 1rem;
        position: relative;
        z-index: 1;
    }

    .action-card .btn {
        position: relative;
        z-index: 1;
    }

    /* Animation */
    @keyframes slideInUp {
        from {
            opacity: 0;
            transform: translateY(40px);
        }
        to {
            opacity: 1;
            transform: translateY(0);
        }
    }

    .animate-in {
        animation: slideInUp 0.7s ease-out forwards;
    }

    .animate-delay-1 { animation-delay: 0.1s; opacity: 0; }
    .animate-delay-2 { animation-delay: 0.2s; opacity: 0; }
    .animate-delay-3 { animation-delay: 0.3s; opacity: 0; }
    .animate-delay-4 { animation-delay: 0.4s; opacity: 0; }

    /* Responsive */
    @media (max-width: 768px) {
        .designer-header h1 {
            font-size: 2rem;
        }
        .stat-card .display-4 {
            font-size: 2.25rem;
        }
    }
</style>
@endpush

@section('content')
<!-- Navbar -->
<nav class="navbar navbar-expand-lg navbar-custom mb-4">
    <div class="container">
        <a class="navbar-brand" href="{{ url('/designer/dashboard') }}">
            <i class="bi bi-palette2"></i>
            Brisas Gems
        </a>
        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
            <span class="navbar-toggler-icon"></span>
        </button>
        <div class="collapse navbar-collapse" id="navbarNav">
            <ul class="navbar-nav me-auto mb-2 mb-lg-0">
                <li class="nav-item">
                    <a class="nav-link" href="{{ url('/designer/disenos') }}">
                        <i class="bi bi-brush me-1"></i>Mis Diseños
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="{{ url('/designer/renders') }}">
                        <i class="bi bi-cube me-1"></i>Renders 3D
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="{{ url('/designer/pedidos') }}">
                        <i class="bi bi-clipboard-check me-1"></i>Pedidos
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="{{ url('/designer/comunicacion') }}">
                        <i class="bi bi-chat-dots me-1"></i>Comunicación
                    </a>
                </li>
            </ul>
            <ul class="navbar-nav ms-auto">
                <li class="nav-item dropdown">
                    <a class="nav-link dropdown-toggle" href="#" role="button" data-bs-toggle="dropdown">
                        <i class="bi bi-person-circle me-1"></i>{{ Session::get('user_name', 'Diseñador') }}
                    </a>
                    <ul class="dropdown-menu dropdown-menu-end">
                        <li>
                            <a class="dropdown-item" href="{{ url('/perfil') }}">
                                <i class="bi bi-person me-2"></i>Mi Perfil
                            </a>
                        </li>
                        <li><hr class="dropdown-divider"></li>
                        <li>
                            <a class="dropdown-item" href="{{ url('/logout') }}">
                                <i class="bi bi-box-arrow-right me-2"></i>Cerrar Sesión
                            </a>
                        </li>
                    </ul>
                </li>
            </ul>
        </div>
    </div>
</nav>

<main class="container mt-4 pb-5">
    <!-- Header -->
    <div class="designer-header animate-in">
        <div class="d-flex justify-content-between align-items-center flex-wrap gap-3">
            <div>
                <h1><i class="bi bi-palette2 me-2"></i>Espacio Creativo</h1>
                <p class="mb-0">¡Hola {{ Session::get('user_name', 'Diseñador') }}! Dale vida a nuevas creaciones hoy ✨</p>
            </div>
            <div>
                <span class="role-badge">
                    <i class="bi bi-brush-fill"></i>
                    Diseñador
                </span>
            </div>
        </div>
    </div>

    <!-- Mis Actividades -->
    <h2 class="section-header animate-in animate-delay-1">Mis Actividades</h2>
    <div class="row row-cols-1 row-cols-sm-2 row-cols-lg-4 g-3 g-md-4 mb-5">
        <div class="col animate-in animate-delay-1">
            <a href="{{ url('/designer/disenos') }}" class="stat-card">
                <div class="card">
                    <div class="icon-bg bg-primary-gradient"></div>
                    <div class="card-body text-center">
                        <div class="icon-wrapper bg-primary-gradient mx-auto">
                            <i class="bi bi-palette2 text-white"></i>
                        </div>
                        <p class="card-text">Diseños Activos</p>
                        <h2 class="display-4 text-primary">{{ $data['disenosActivos'] ?? 0 }}</h2>
                    </div>
                </div>
            </a>
        </div>

        <div class="col animate-in animate-delay-2">
            <a href="{{ url('/designer/renders') }}" class="stat-card">
                <div class="card">
                    <div class="icon-bg bg-info-gradient"></div>
                    <div class="card-body text-center">
                        <div class="icon-wrapper bg-info-gradient mx-auto">
                            <i class="bi bi-cube text-white"></i>
                        </div>
                        <p class="card-text">Renders Pendientes</p>
                        <h2 class="display-4 text-info">{{ $data['rendersPendientes'] ?? 0 }}</h2>
                    </div>
                </div>
            </a>
        </div>

        <div class="col animate-in animate-delay-3">
            <a href="{{ url('/designer/comunicacion') }}" class="stat-card">
                <div class="card">
                    <div class="icon-bg bg-warning-gradient"></div>
                    <div class="card-body text-center">
                        <div class="icon-wrapper bg-warning-gradient mx-auto">
                            <i class="bi bi-chat-dots text-white"></i>
                        </div>
                        <p class="card-text">Mensajes</p>
                        <h2 class="display-4 text-warning">{{ $data['comunicacionesPendientes'] ?? 0 }}</h2>
                    </div>
                </div>
            </a>
        </div>

        <div class="col animate-in animate-delay-4">
            <a href="{{ url('/designer/pedidos') }}" class="stat-card">
                <div class="card">
                    <div class="icon-bg bg-success-gradient"></div>
                    <div class="card-body text-center">
                        <div class="icon-wrapper bg-success-gradient mx-auto">
                            <i class="bi bi-clipboard-check text-white"></i>
                        </div>
                        <p class="card-text">Pedidos Asignados</p>
                        <h2 class="display-4 text-success">{{ $data['pedidosAsignados'] ?? 0 }}</h2>
                    </div>
                </div>
            </a>
        </div>
    </div>

    <!-- Acciones Rápidas -->
    <h2 class="section-header animate-in animate-delay-2">Acciones Rápidas</h2>
    <div class="row g-3 g-md-4">
        <div class="col-md-6 animate-in animate-delay-1">
            <a href="{{ url('/designer/nuevo-diseno') }}" class="action-card">
                <div class="d-flex align-items-start gap-3">
                    <div class="icon-wrapper bg-primary-gradient" style="width: 60px; height: 60px; border-radius: 12px; flex-shrink: 0;">
                        <i class="bi bi-plus-circle text-white" style="font-size: 1.75rem;"></i>
                    </div>
                    <div class="flex-grow-1">
                        <h6><i class="bi bi-plus-circle me-2"></i>Nuevo Diseño</h6>
                        <p class="mb-3">Comienza un diseño desde cero con todas las herramientas disponibles</p>
                        <span class="btn btn-primary btn-sm">Comenzar Ahora</span>
                    </div>
                </div>
            </a>
        </div>

        <div class="col-md-6 animate-in animate-delay-2">
            <a href="{{ url('/designer/renders') }}" class="action-card">
                <div class="d-flex align-items-start gap-3">
                    <div class="icon-wrapper bg-info-gradient" style="width: 60px; height: 60px; border-radius: 12px; flex-shrink: 0;">
                        <i class="bi bi-clock-history text-white" style="font-size: 1.75rem;"></i>
                    </div>
                    <div class="flex-grow-1">
                        <h6><i class="bi bi-clock-history me-2"></i>Revisar Renders</h6>
                        <p class="mb-3">Revisa y aprueba los renders 3D pendientes de tus diseños</p>
                        <span class="btn btn-info btn-sm">Revisar Ahora</span>
                    </div>
                </div>
            </a>
        </div>

        <div class="col-md-6 animate-in animate-delay-3">
            <a href="{{ url('/designer/comunicacion') }}" class="action-card">
                <div class="d-flex align-items-start gap-3">
                    <div class="icon-wrapper bg-warning-gradient" style="width: 60px; height: 60px; border-radius: 12px; flex-shrink: 0;">
                        <i class="bi bi-chat-square-text text-white" style="font-size: 1.75rem;"></i>
                    </div>
                    <div class="flex-grow-1">
                        <h6><i class="bi bi-chat-square-text me-2"></i>Comunicación con Clientes</h6>
                        <p class="mb-3">Revisa mensajes y retroalimentación sobre tus diseños</p>
                        <span class="btn btn-warning btn-sm">Ver Mensajes</span>
                    </div>
                </div>
            </a>
        </div>

        <div class="col-md-6 animate-in animate-delay-4">
            <a href="{{ url('/designer/pedidos') }}" class="action-card">
                <div class="d-flex align-items-start gap-3">
                    <div class="icon-wrapper bg-success-gradient" style="width: 60px; height: 60px; border-radius: 12px; flex-shrink: 0;">
                        <i class="bi bi-list-check text-white" style="font-size: 1.75rem;"></i>
                    </div>
                    <div class="flex-grow-1">
                        <h6><i class="bi bi-list-check me-2"></i>Gestionar Pedidos</h6>
                        <p class="mb-3">Administra todos los pedidos que tienes asignados</p>
                        <span class="btn btn-success btn-sm">Ver Pedidos</span>
                    </div>
                </div>
            </a>
        </div>
    </div>
</main>
@endsection