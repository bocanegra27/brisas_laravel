@extends('layouts.app')

@section('title', 'Mi Panel - Brisas Gems')

@push('styles')
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700;800&family=Satisfy&display=swap" rel="stylesheet">

<style>
    :root {
        --primary: #009688;
        --primary-dark: #00796b;
        --primary-light: #b2dfdb;
        --accent: #ff6b9d;
        --accent-light: #ffc1e3;
        --surface: #ffffff;
        --background: #f8f9fa;
        --text-primary: #2d3748;
        --text-secondary: #718096;
        --shadow-sm: 0 2px 8px rgba(0,0,0,0.05);
        --shadow-md: 0 4px 16px rgba(0,0,0,0.08);
        --shadow-lg: 0 12px 32px rgba(0,0,0,0.12);
        --transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
        --radius: 16px;
    }

    body {
        font-family: 'Poppins', sans-serif;
        background: linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%);
        color: var(--text-primary);
    }

    /* Navbar Amigable */
    .navbar-custom {
        background: white;
        box-shadow: var(--shadow-sm);
        padding: 1rem 0;
        border-bottom: 3px solid var(--primary);
    }

    .navbar-custom .navbar-brand {
        font-weight: 700;
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
        position: relative;
    }

    .navbar-custom .nav-link:hover {
        background: linear-gradient(135deg, rgba(0, 150, 136, 0.1), rgba(255, 107, 157, 0.1));
        color: var(--primary);
    }

    .navbar-custom .dropdown-menu {
        border: none;
        box-shadow: var(--shadow-md);
        border-radius: 12px;
        padding: 0.5rem;
        margin-top: 0.5rem;
    }

    .navbar-custom .dropdown-item {
        border-radius: 8px;
        padding: 0.75rem 1rem;
        transition: var(--transition);
    }

    .navbar-custom .dropdown-item:hover {
        background: linear-gradient(135deg, rgba(0, 150, 136, 0.1), rgba(255, 107, 157, 0.1));
        color: var(--primary);
    }

    /* Welcome Section */
    .welcome-section {
        background: linear-gradient(135deg, var(--primary) 0%, #00897b 50%, var(--primary-dark) 100%);
        border-radius: var(--radius);
        padding: 3rem 2rem;
        margin-bottom: 2.5rem;
        box-shadow: var(--shadow-lg);
        position: relative;
        overflow: hidden;
        color: white;
    }

    .welcome-section::before {
        content: '💎';
        position: absolute;
        top: -20px;
        right: -20px;
        font-size: 10rem;
        opacity: 0.1;
        transform: rotate(-15deg);
    }

    .welcome-section::after {
        content: '';
        position: absolute;
        bottom: -50px;
        left: -50px;
        width: 200px;
        height: 200px;
        background: radial-gradient(circle, rgba(255,255,255,0.2) 0%, transparent 70%);
        border-radius: 50%;
    }

    .welcome-section h2 {
        font-weight: 800;
        font-size: 2.5rem;
        margin-bottom: 0.5rem;
        position: relative;
        z-index: 1;
    }

    .welcome-section .wave {
        display: inline-block;
        animation: wave 1s ease-in-out infinite;
        transform-origin: 70% 70%;
    }

    @keyframes wave {
        0%, 100% { transform: rotate(0deg); }
        25% { transform: rotate(20deg); }
        75% { transform: rotate(-20deg); }
    }

    .welcome-section p {
        font-size: 1.125rem;
        opacity: 0.95;
        margin-bottom: 1.5rem;
        position: relative;
        z-index: 1;
    }

    .welcome-section .btn-light {
        background: white;
        color: var(--primary);
        font-weight: 600;
        padding: 0.875rem 2rem;
        border-radius: 50px;
        border: none;
        box-shadow: 0 4px 12px rgba(0,0,0,0.15);
        transition: var(--transition);
        position: relative;
        z-index: 1;
    }

    .welcome-section .btn-light:hover {
        transform: translateY(-3px);
        box-shadow: 0 8px 20px rgba(0,0,0,0.2);
        background: linear-gradient(135deg, white, #f8f9fa);
    }

    /* Section Headers */
    .section-header {
        font-weight: 700;
        font-size: 1.5rem;
        color: var(--text-primary);
        margin-bottom: 1.5rem;
        display: flex;
        align-items: center;
        gap: 0.75rem;
    }

    .section-header i {
        color: var(--primary);
    }

    /* Stat Cards Modernas */
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
    }

    .stat-card .card::before {
        content: '';
        position: absolute;
        top: 0;
        left: 0;
        right: 0;
        height: 5px;
        background: linear-gradient(90deg, var(--primary) 0%, var(--accent) 100%);
        transform: scaleX(0);
        transform-origin: left;
        transition: transform 0.4s ease;
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
        text-align: center;
        position: relative;
    }

    .stat-card .icon-wrapper {
        width: 70px;
        height: 70px;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        margin: 0 auto 1rem;
        position: relative;
        transition: var(--transition);
    }

    .stat-card:hover .icon-wrapper {
        transform: scale(1.15);
    }

    .stat-card .bi {
        font-size: 2rem;
    }

    .stat-card .card-text {
        font-size: 0.875rem;
        font-weight: 600;
        color: var(--text-secondary);
        margin-bottom: 0.75rem;
        text-transform: uppercase;
        letter-spacing: 0.5px;
    }

    .stat-card .display-4 {
        font-weight: 800;
        font-size: 2.75rem;
        margin: 0;
        line-height: 1;
    }

    /* Color Variations */
    .bg-primary-soft { background: linear-gradient(135deg, rgba(0, 150, 136, 0.15), rgba(0, 150, 136, 0.05)); }
    .bg-info-soft { background: linear-gradient(135deg, rgba(59, 130, 246, 0.15), rgba(59, 130, 246, 0.05)); }
    .bg-success-soft { background: linear-gradient(135deg, rgba(34, 197, 94, 0.15), rgba(34, 197, 94, 0.05)); }
    .bg-accent-soft { background: linear-gradient(135deg, rgba(255, 107, 157, 0.15), rgba(255, 107, 157, 0.05)); }

    /* Quick Actions */
    .quick-action-btn {
        background: white;
        border: 2px solid var(--primary);
        color: var(--primary);
        border-radius: 12px;
        padding: 1.5rem;
        transition: var(--transition);
        text-decoration: none;
        display: flex;
        flex-direction: column;
        align-items: center;
        justify-content: center;
        gap: 0.75rem;
        height: 100%;
        min-height: 150px;
        box-shadow: var(--shadow-sm);
    }

    .quick-action-btn:hover {
        background: linear-gradient(135deg, var(--primary) 0%, var(--primary-dark) 100%);
        color: white;
        border-color: var(--primary-dark);
        transform: translateY(-5px);
        box-shadow: var(--shadow-md);
    }

    .quick-action-btn i {
        font-size: 2.5rem;
        transition: var(--transition);
    }

    .quick-action-btn:hover i {
        transform: scale(1.2);
    }

    .quick-action-btn span {
        font-weight: 600;
        font-size: 0.875rem;
    }

    /* User Info Card */
    .user-info-card {
        background: white;
        border: none;
        border-radius: var(--radius);
        box-shadow: var(--shadow-sm);
        overflow: hidden;
    }

    .user-info-card .card-body {
        padding: 2rem;
    }

    .user-avatar {
        width: 80px;
        height: 80px;
        background: linear-gradient(135deg, var(--primary) 0%, var(--accent) 100%);
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        color: white;
        font-size: 2.5rem;
        box-shadow: var(--shadow-md);
    }

    .user-stats {
        background: linear-gradient(135deg, #f8f9fa, #e9ecef);
        border-radius: 12px;
        padding: 1rem;
        margin-top: 1.5rem;
    }

    .user-stat-item {
        text-align: center;
        padding: 0.5rem;
    }

    .user-stat-item .h5 {
        font-weight: 800;
        color: var(--primary);
        margin-bottom: 0.25rem;
    }

    .user-stat-item small {
        color: var(--text-secondary);
        font-weight: 500;
    }

    .user-stat-divider {
        width: 1px;
        background: #dee2e6;
        height: 40px;
        margin: auto 0;
    }

    /* Badges */
    .badge-custom {
        background: linear-gradient(135deg, var(--primary) 0%, var(--primary-dark) 100%);
        color: white;
        padding: 0.5rem 1rem;
        border-radius: 50px;
        font-weight: 600;
        font-size: 0.75rem;
        letter-spacing: 0.5px;
    }

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

    /* Responsive */
    @media (max-width: 768px) {
        .welcome-section h2 {
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
<nav class="navbar navbar-expand-lg navbar-custom mb-4">
    <div class="container">
        <a class="navbar-brand" href="{{ url('/user/dashboard') }}">
            <i class="bi bi-gem"></i>
            Brisas Gems
        </a>
        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
            <span class="navbar-toggler-icon"></span>
        </button>
        <div class="collapse navbar-collapse" id="navbarNav">
            <ul class="navbar-nav me-auto mb-2 mb-lg-0">
                <li class="nav-item">
                    <a class="nav-link" href="{{ url('/personalizar') }}">
                        <i class="bi bi-palette2 me-1"></i>Personalizar Joya
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="{{ url('/mis-pedidos') }}">
                        <i class="bi bi-bag-check me-1"></i>Mis Pedidos
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="{{ url('/inspiracion') }}">
                        <i class="bi bi-images me-1"></i>Galería
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="{{ url('/contacto') }}">
                        <i class="bi bi-headset me-1"></i>Contacto
                    </a>
                </li>
            </ul>
            <ul class="navbar-nav ms-auto">
                <li class="nav-item dropdown">
                    <a class="nav-link dropdown-toggle" href="#" role="button" data-bs-toggle="dropdown">
                        <i class="bi bi-person-circle me-1"></i>{{ Session::get('user_name', 'Usuario') }}
                    </a>
                    <ul class="dropdown-menu dropdown-menu-end">
                        <li>
                            <a class="dropdown-item" href="{{ url('/perfil') }}">
                                <i class="bi bi-person me-2"></i>Mi Perfil
                            </a>
                        </li>
                        <li>
                            <a class="dropdown-item" href="{{ url('/mis-personalizaciones') }}">
                                <i class="bi bi-palette me-2"></i>Mis Personalizaciones
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
    <!-- Welcome Section -->
    <div class="welcome-section animate-in">
        <div class="row align-items-center">
            <div class="col-md-8">
                <h2>¡Bienvenido, {{ Session::get('user_name', 'Usuario') }}! <span class="wave">👋</span></h2>
                <p>Gestiona tus pedidos y crea las joyas de tus sueños</p>
            </div>
            <div class="col-md-4 text-md-end mt-3 mt-md-0">
                <a href="{{ url('/personalizar') }}" class="btn btn-light btn-lg">
                    <i class="bi bi-plus-circle me-2"></i>Crear Nueva Joya
                </a>
            </div>
        </div>
    </div>

    <!-- Mis Actividades -->
    <h2 class="section-header animate-in animate-delay-1">
        <i class="bi bi-bar-chart-fill"></i>
        Mis Actividades
    </h2>
    <div class="row row-cols-1 row-cols-md-3 g-3 g-md-4 mb-5">
        <div class="col animate-in animate-delay-1">
            <a href="{{ url('/mis-pedidos') }}" class="stat-card">
                <div class="card">
                    <div class="card-body">
                        <div class="icon-wrapper bg-primary-soft">
                            <i class="bi bi-clock-history text-primary"></i>
                        </div>
                        <p class="card-text">Pedidos Activos</p>
                        <h2 class="display-4 text-primary">{{ $data['misPedidosActivos'] ?? 0 }}</h2>
                    </div>
                </div>
            </a>
        </div>

        <div class="col animate-in animate-delay-2">
            <a href="{{ url('/mis-personalizaciones') }}" class="stat-card">
                <div class="card">
                    <div class="card-body">
                        <div class="icon-wrapper bg-info-soft">
                            <i class="bi bi-palette2 text-info"></i>
                        </div>
                        <p class="card-text">Mis Personalizaciones</p>
                        <h2 class="display-4 text-info">{{ $data['misPersonalizaciones'] ?? 0 }}</h2>
                    </div>
                </div>
            </a>
        </div>

        <div class="col animate-in animate-delay-3">
            <a href="{{ url('/historial') }}" class="stat-card">
                <div class="card">
                    <div class="card-body">
                        <div class="icon-wrapper bg-success-soft">
                            <i class="bi bi-check-circle text-success"></i>
                        </div>
                        <p class="card-text">Pedidos Completados</p>
                        <h2 class="display-4 text-success">{{ $data['pedidosCompletados'] ?? 0 }}</h2>
                    </div>
                </div>
            </a>
        </div>
    </div>

    <div class="row g-4">
        <!-- Acciones Rápidas -->
        <div class="col-lg-6 animate-in animate-delay-2">
            <h2 class="section-header">
                <i class="bi bi-lightning-fill"></i>
                Acciones Rápidas
            </h2>
            <div class="card user-info-card">
                <div class="card-body">
                    <div class="row g-3">
                        <div class="col-6">
                            <a href="{{ url('/personalizar') }}" class="quick-action-btn">
                                <i class="bi bi-plus-circle"></i>
                                <span>Personalizar Joya</span>
                            </a>
                        </div>
                        <div class="col-6">
                            <a href="{{ url('/inspiracion') }}" class="quick-action-btn">
                                <i class="bi bi-images"></i>
                                <span>Ver Inspiración</span>
                            </a>
                        </div>
                        <div class="col-6">
                            <a href="{{ url('/mis-pedidos') }}" class="quick-action-btn">
                                <i class="bi bi-list-check"></i>
                                <span>Mis Pedidos</span>
                            </a>
                        </div>
                        <div class="col-6">
                            <a href="{{ url('/contacto') }}" class="quick-action-btn">
                                <i class="bi bi-headset"></i>
                                <span>Soporte</span>
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Mi Información -->
        <div class="col-lg-6 animate-in animate-delay-3">
            <h2 class="section-header">
                <i class="bi bi-person-circle"></i>
                Mi Información
            </h2>
            <div class="card user-info-card">
                <div class="card-body">
                    <div class="d-flex align-items-center mb-3">
                        <div class="user-avatar flex-shrink-0">
                            <i class="bi bi-person"></i>
                        </div>
                        <div class="flex-grow-1 ms-3">
                            <h6 class="mb-1 fw-bold">{{ Session::get('user_name', 'Usuario') }}</h6>
                            <p class="text-muted small mb-1">{{ Session::get('user_email', 'usuario@ejemplo.com') }}</p>
                            <span class="badge-custom">Cliente Premium</span>
                        </div>
                    </div>

                    <div class="user-stats">
                        <div class="row align-items-center">
                            <div class="col-4">
                                <div class="user-stat-item">
                                    <div class="h5 mb-0">{{ $data['misPedidosActivos'] ?? 0 }}</div>
                                    <small>Activos</small>
                                </div>
                            </div>
                            <div class="col-auto px-0">
                                <div class="user-stat-divider"></div>
                            </div>
                            <div class="col-4">
                                <div class="user-stat-item">
                                    <div class="h5 mb-0">{{ $data['misPersonalizaciones'] ?? 0 }}</div>
                                    <small>Diseños</small>
                                </div>
                            </div>
                            <div class="col-auto px-0">
                                <div class="user-stat-divider"></div>
                            </div>
                            <div class="col-3">
                                <div class="user-stat-item">
                                    <div class="h5 mb-0">{{ $data['pedidosCompletados'] ?? 0 }}</div>
                                    <small>Completados</small>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</main>
@endsection