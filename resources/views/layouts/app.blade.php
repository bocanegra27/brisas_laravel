<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <meta name="csrf-token" content="{{ csrf_token() }}">
    
    <title>@yield('title', 'Brisas Gems')</title>
    <link rel="icon" href="{{ asset('assets/icons/icono.png') }}" />
    <meta name="description" content="@yield('description', 'Brisas Gems: personalización de joyas, inspiración y seguimiento de pedidos.')" />
    
    <!-- CSS Base -->
    <link rel="stylesheet" href="{{ asset('assets/css/bootstrap.min.css') }}" />
    <link rel="stylesheet" href="{{ asset('assets/css/main.css') }}" />
    <link rel="stylesheet" href="{{ asset('assets/css/header.css') }}" />
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    
    <!-- Google Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;600&display=swap" rel="stylesheet">
    
    <!-- Model Viewer (para 3D - solo si la página lo necesita) -->
    @stack('head-scripts')
    
    @stack('styles')
</head>
<body>
    @include('layouts.header')
    
    <main>
        @yield('content')
    </main>
    
    @include('layouts.footer')
    
    <!-- Scripts Base -->
    <script src="{{ asset('assets/js/bootstrap.bundle.min.js') }}"></script>
    
    <script>
        // Menú usuario
        const iconoUsuario = document.getElementById('icono-usuario');
        const menuUsuario = document.getElementById('menu-usuario');
        if (iconoUsuario && menuUsuario) {
            iconoUsuario.addEventListener('click', () => menuUsuario.classList.toggle('activo'));
            document.addEventListener('click', e => {
                if (!iconoUsuario.contains(e.target) && !menuUsuario.contains(e.target)) {
                    menuUsuario.classList.remove('activo');
                }
            });
        }
    </script>
    
    @stack('scripts')
</body>
</html>