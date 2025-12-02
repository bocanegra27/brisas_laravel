<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <script type="module" src="https://ajax.googleapis.com/ajax/libs/model-viewer/3.3.0/model-viewer.min.js"></script>
    
    {{-- 🟢 CRÍTICO: CSRF Token para peticiones AJAX --}}
    <meta name="csrf-token" content="{{ csrf_token() }}">
    
    {{-- El título será dinámico según la página --}}
    <title>@yield('title', 'Emerald System')</title>

    {{-- Bootstrap CSS --}}
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    
    {{-- Bootstrap Icons --}}
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    
    {{-- Google Fonts --}}
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;600&display=swap" rel="stylesheet">

    {{-- Vite: Compilación de assets --}}
    @vite(['resources/css/app.css', 'resources/js/app.js', 'resources/css/pedidos.css'])
    
    {{-- Stack para CSS adicional --}}
    @stack('styles')
</head>
<body>

    <header class="bg-white shadow-sm py-3 mb-4">
        <div class="container">
            <h1 class="h3 mb-0 fw-bold" style="color: #009b77;">
                {{-- Aquí inyectaremos el título de cada módulo --}}
                @yield('header')
            </h1>
        </div>
    </header>

    <main class="container">
        {{-- Aquí se inyectará el contenido principal (formularios, tablas, etc.) --}}
        @yield('content')
    </main>

    {{-- Bootstrap JS (DEBE ir ANTES de scripts personalizados) --}}
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    
    {{-- Stack para scripts adicionales --}}
    @stack('scripts')

</body>
</html>