<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <title>@yield('title', 'Brisas Gems')</title>
    <link rel="icon" href="{{ asset('assets/icons/icono.png') }}" />
    
    <!-- CSS -->
    <link rel="stylesheet" href="{{ asset('assets/css/bootstrap.min.css') }}" />
    <link rel="stylesheet" href="{{ asset('assets/css/main.css') }}" />
    <link rel="stylesheet" href="{{ asset('assets/css/header.css') }}" />
    @stack('styles')
    
    <meta name="description" content="@yield('description', 'Brisas Gems: personalización de joyas, inspiración y seguimiento de pedidos.')" />
</head>
<body>

    @include('layouts.header')

    <main>
        @yield('content')
    </main>

    @include('layouts.footer')

    <!-- Scripts -->
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