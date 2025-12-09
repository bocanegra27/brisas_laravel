{{-- ============================================
     HEADER ÚNICO ULTRAMINIMALISTA - BRISAS GEMS
     Se adapta según autenticación y rol del usuario
     ============================================ --}}

@php
    // Detectar estado de autenticación y datos del usuario
    $isAuthenticated = Session::has('jwt_token');
    $userRole = Session::get('user_role', null);
    $userName = Session::get('user_name', 'Usuario');
    $userEmail = Session::get('user_email', '');
    $dashboardUrl = Session::get('dashboard_url', '/dashboard');
    
    // Normalizar rol para comparaciones
    $isAdmin = ($userRole === 'ROLE_ADMINISTRADOR');
    $isDesigner = ($userRole === 'ROLE_DISEÑADOR');
    $isUser = ($userRole === 'ROLE_USUARIO');
    
    // Determinar URL del logo según contexto
    if ($isAdmin) {
        $logoUrl = '/admin/dashboard';
    } elseif ($isDesigner) {
        $logoUrl = '/designer/dashboard';
    } elseif ($isUser) {
        $logoUrl = '/user/dashboard';
    } else {
        $logoUrl = '/';
    }
    
    // Ruta actual para marcar links activos
    $currentRoute = Request::path();
@endphp

<header class="header-minimal">
    <div class="header-minimal__container">
        
        {{-- ===== LOGO ===== --}}
        <a href="{{ url($logoUrl) }}" class="header-minimal__logo">
            <img src="{{ asset('assets/img/logo/logo_120.png') }}" 
                alt="Brisas Gems Logo" 
                class="header-minimal__logo-img">
            <span class="header-minimal__logo-text">Brisas Gems</span>
        </a>

        {{-- ===== HAMBURGER MENU (Mobile) ===== --}}
        <button class="header-minimal__hamburger" id="headerHamburger" aria-label="Menú de navegación">
            <span class="header-minimal__hamburger-line"></span>
            <span class="header-minimal__hamburger-line"></span>
            <span class="header-minimal__hamburger-line"></span>
        </button>

        {{-- ===== NAVEGACIÓN PRINCIPAL ===== --}}
        <nav class="header-minimal__nav" id="headerNav">
            @if(!$isAuthenticated)
                {{-- MENÚ PARA INVITADOS (No autenticados) --}}
                <a href="{{ url('/') }}" 
                   class="header-minimal__nav-link {{ $currentRoute == '/' ? 'header-minimal__nav-link--active' : '' }}">
                    Inicio
                </a>
                <a href="{{ route('personalizar.index') }}"
                   class="header-minimal__nav-link">Personalizar
                </a>
                {{--<a href="{{ url('/inspiracion') }}" 
                   class="header-minimal__nav-link {{ Str::startsWith($currentRoute, 'inspiracion') ? 'header-minimal__nav-link--active' : '' }}">
                    Inspiración
                </a>--}}
                <a href="{{ url('/contacto') }}" 
                   class="header-minimal__nav-link {{ Str::startsWith($currentRoute, 'contacto') ? 'header-minimal__nav-link--active' : '' }}">
                    Contacto
                </a>

            @elseif($isAdmin)
                {{-- MENÚ PARA ADMINISTRADOR --}}
                <a href="{{ url('/admin/dashboard') }}" 
                   class="header-minimal__nav-link {{ Str::startsWith($currentRoute, 'admin/dashboard') ? 'header-minimal__nav-link--active' : '' }}">
                    Dashboard
                </a>
                <a href="{{ route('admin.usuarios.index') }}" 
                   class="header-minimal__nav-link {{ Str::startsWith($currentRoute, 'usuarios') ? 'header-minimal__nav-link--active' : '' }}">
                    Usuarios
                </a>
                <a href="{{ route('admin.mensajes.index') }}" 
                   class="header-minimal__nav-link {{ Str::startsWith($currentRoute, 'admin/contactos') ? 'header-minimal__nav-link--active' : '' }}">
                    Contactos
                </a>
                <a href="{{ route('admin.pedidos.index') }}" 
                   class="header-minimal__nav-link {{ Str::startsWith($currentRoute, 'pedidos') ? 'header-minimal__nav-link--active' : '' }}">
                    Pedidos
                </a>

            @elseif($isDesigner)
                {{-- MENÚ PARA DISEÑADOR --}}
                <a href="{{ url('/designer/dashboard') }}" 
                   class="header-minimal__nav-link {{ Str::startsWith($currentRoute, 'designer/dashboard') ? 'header-minimal__nav-link--active' : '' }}">
                    Dashboard
                </a>
                {{-- Rutas pendientes de implementar --}}
                {{-- <a href="{{ url('/designer/disenos') }}" 
                   class="header-minimal__nav-link {{ Str::startsWith($currentRoute, 'designer/disenos') ? 'header-minimal__nav-link--active' : '' }}">
                    Mis Diseños
                </a>
                <a href="{{ url('/designer/renders') }}" 
                   class="header-minimal__nav-link {{ Str::startsWith($currentRoute, 'designer/renders') ? 'header-minimal__nav-link--active' : '' }}">
                    Renders
                </a> --}}
                <a href="{{ url('/pedidos') }}" 
                   class="header-minimal__nav-link {{ Str::startsWith($currentRoute, 'pedidos') ? 'header-minimal__nav-link--active' : '' }}">
                    Pedidos
                </a>
                {{-- <a href="{{ url('/designer/comunicacion') }}" 
                   class="header-minimal__nav-link {{ Str::startsWith($currentRoute, 'designer/comunicacion') ? 'header-minimal__nav-link--active' : '' }}">
                    Comunicación
                </a> --}}

            @elseif($isUser)
                {{-- MENÚ PARA USUARIO NORMAL --}}
                <a href="{{ url('/') }}" 
                   class="header-minimal__nav-link {{ $currentRoute == '/' ? 'header-minimal__nav-link--active' : '' }}">
                    Inicio
                </a>
                <a href="{{ url('/personalizar') }}" 
                   class="header-minimal__nav-link {{ Str::startsWith($currentRoute, 'personalizar') ? 'header-minimal__nav-link--active' : '' }}">
                    Personalizar
                </a>
                <a href="{{ url('/contacto') }}" 
                   class="header-minimal__nav-link {{ Str::startsWith($currentRoute, 'contacto') ? 'header-minimal__nav-link--active' : '' }}">
                    Contacto
                </a>
                <a href="{{ url('/mis-pedidos') }}" 
                   class="header-minimal__nav-link {{ Str::startsWith($currentRoute, 'mis-pedidos') ? 'header-minimal__nav-link--active' : '' }}">
                    Mis Pedidos
                </a>
            @endif
        </nav>

        {{-- ===== ACCIONES (Derecha) ===== --}}
        <div class="header-minimal__actions">
            @if(!$isAuthenticated)
                {{-- BOTÓN INICIAR SESIÓN (Invitados) --}}
                <a href="{{ url('/login') }}" class="header-minimal__login-btn">
                    Iniciar Sesión
                </a>

            @else
                {{-- DROPDOWN USUARIO (Autenticados) --}}
                <div class="header-minimal__user-dropdown" id="userDropdown">
                    <button class="header-minimal__user-toggle" id="userToggle" aria-expanded="false" aria-haspopup="true">
                        <i class="bi bi-person-circle header-minimal__user-icon"></i>
                        <span class="header-minimal__user-name">{{ $userName }}</span>
                        <i class="bi bi-chevron-down header-minimal__user-chevron"></i>
                    </button>

                    <div class="header-minimal__dropdown-menu">
                        {{-- Opción: Mi Perfil (todos los roles) --}}
                        {{-- Pendiente de implementar --}}
                        {{-- <a href="{{ url('/perfil') }}" class="header-minimal__dropdown-item">
                            <i class="bi bi-person header-minimal__dropdown-icon"></i>
                            Mi Perfil
                        </a> --}}

                        {{-- Opciones adicionales según rol --}}
                        @if($isUser)
                            {{-- Usuario: Mis Pedidos --}}
                            <a href="{{ url('/mis-pedidos') }}" class="header-minimal__dropdown-item">
                                <i class="bi bi-bag-check header-minimal__dropdown-icon"></i>
                                Mis Pedidos
                            </a>
                            {{-- Pendiente: Mis Personalizaciones --}}
                            {{-- <a href="{{ url('/mis-personalizaciones') }}" class="header-minimal__dropdown-item">
                                <i class="bi bi-palette2 header-minimal__dropdown-icon"></i>
                                Mis Personalizaciones
                            </a> --}}
                        @endif

                        {{-- Divider antes de cerrar sesión --}}
                        @if($isUser)
                            <div class="header-minimal__dropdown-divider"></div>
                        @endif

                        {{-- Cerrar Sesión (todos los roles) --}}
                        <a href="{{ url('/logout') }}" class="header-minimal__dropdown-item">
                            <i class="bi bi-box-arrow-right header-minimal__dropdown-icon"></i>
                            Cerrar Sesión
                        </a>
                    </div>
                </div>
            @endif
        </div>

    </div>
</header>

{{-- ===== JAVASCRIPT PARA INTERACTIVIDAD ===== --}}
@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    // ===== Dropdown Usuario =====
    const userDropdown = document.getElementById('userDropdown');
    const userToggle = document.getElementById('userToggle');
    
    if (userToggle && userDropdown) {
        // Toggle dropdown al hacer click
        userToggle.addEventListener('click', function(e) {
            e.stopPropagation();
            const isOpen = userDropdown.classList.contains('show');
            userDropdown.classList.toggle('show');
            userToggle.setAttribute('aria-expanded', !isOpen);
        });

        // Cerrar dropdown al hacer click fuera
        document.addEventListener('click', function(e) {
            if (!userDropdown.contains(e.target)) {
                userDropdown.classList.remove('show');
                userToggle.setAttribute('aria-expanded', 'false');
            }
        });

        // Prevenir que el dropdown se cierre al hacer click dentro
        userDropdown.addEventListener('click', function(e) {
            if (e.target.tagName === 'A') {
                // Permitir que los links funcionen normalmente
                return;
            }
            e.stopPropagation();
        });
    }

    // ===== Hamburger Menu (Mobile) =====
    const hamburger = document.getElementById('headerHamburger');
    const nav = document.getElementById('headerNav');
    
    if (hamburger && nav) {
        hamburger.addEventListener('click', function() {
            hamburger.classList.toggle('active');
            nav.classList.toggle('active');
            
            // Accesibilidad: actualizar aria-expanded
            const isExpanded = nav.classList.contains('active');
            hamburger.setAttribute('aria-expanded', isExpanded);
        });

        // Cerrar menú móvil al hacer click en un link
        const navLinks = nav.querySelectorAll('.header-minimal__nav-link');
        navLinks.forEach(link => {
            link.addEventListener('click', function() {
                hamburger.classList.remove('active');
                nav.classList.remove('active');
                hamburger.setAttribute('aria-expanded', 'false');
            });
        });

        // Cerrar menú móvil al hacer click fuera
        document.addEventListener('click', function(e) {
            if (!nav.contains(e.target) && !hamburger.contains(e.target)) {
                hamburger.classList.remove('active');
                nav.classList.remove('active');
                hamburger.setAttribute('aria-expanded', 'false');
            }
        });
    }

    // ===== Cerrar menús al presionar ESC =====
    document.addEventListener('keydown', function(e) {
        if (e.key === 'Escape') {
            // Cerrar dropdown usuario
            if (userDropdown && userDropdown.classList.contains('show')) {
                userDropdown.classList.remove('show');
                if (userToggle) userToggle.setAttribute('aria-expanded', 'false');
            }
            
            // Cerrar menú móvil
            if (hamburger && hamburger.classList.contains('active')) {
                hamburger.classList.remove('active');
                nav.classList.remove('active');
                hamburger.setAttribute('aria-expanded', 'false');
            }
        }
    });
});
</script>
@endpush