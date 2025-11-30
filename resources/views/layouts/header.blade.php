<header class="encabezado">
  <div class="contenedor-header">
    <!-- Logo centrado -->
    <div class="logo-centro">
      <a href="{{ url('/') }}">
        <img src="{{ asset('assets/img/logo/logo.png') }}" alt="Logo Brisas Gems">
      </a>
    </div>

    <!-- Menú izquierdo -->
    <nav class="nav-izquierda">
      @auth
        @if(auth()->user()->rol_id === 2)
          {{-- Menú Admin --}}
          <a href="{{ url('/gestion-usuarios') }}" class="{{ request()->is('gestion-usuarios*') ? 'activo' : '' }}">GESTIÓN USUARIO</a>
          <a href="{{ url('/admin/inspiracion') }}" class="{{ request()->is('admin/inspiracion*') ? 'activo' : '' }}">GESTIÓN INSPIRACIÓN</a>
          <a href="{{ url('/admin/opciones') }}" class="{{ request()->is('admin/opciones*') ? 'activo' : '' }}">GESTIÓN PERSONALIZACIÓN</a>
          <a href="{{ url('/admin/pedidos') }}" class="{{ request()->is('admin/pedidos*') ? 'activo' : '' }}">GESTIÓN PEDIDOS</a>
        @else
          {{-- Menú Usuario normal --}}
          <a href="{{ url('/personalizar') }}" class="{{ request()->is('personalizar*') ? 'activo' : '' }}">PERSONALIZACIÓN</a>
          <a href="{{ url('/inspiracion') }}" class="{{ request()->is('inspiracion*') ? 'activo' : '' }}">INSPIRACIÓN</a>
        @endif
      @else
        {{-- Menú invitado --}}
        <a href="{{ url('/personalizar') }}" class="{{ request()->is('personalizar*') ? 'activo' : '' }}">PERSONALIZACIÓN</a>
        <a href="{{ url('/inspiracion') }}" class="{{ request()->is('inspiracion*') ? 'activo' : '' }}">INSPIRACIÓN</a>
      @endauth
    </nav>

    <!-- Íconos a la derecha -->
    <div class="menu-derecha">
      <a href="#"><img src="{{ asset('assets/img/icons/gem.svg') }}" alt="Favoritos" class="icono"></a>
      <a href="#"><img src="{{ asset('assets/img/icons/bluesky.svg') }}" alt="Carrito" class="icono"></a>

      <div class="perfil-wrapper">
        @auth
          <div class="avatar" id="icono-usuario">
            {{ strtoupper(substr(auth()->user()->usu_nombre, 0, 1)) }}
          </div>

          <div class="menu-usuario" id="menu-usuario">
            <p class="px-3 fw-bold">{{ auth()->user()->usu_nombre }}</p>

            @if(auth()->user()->rol_id === 1)
              {{-- Usuario normal --}}
              <a href="{{ url('/usuario/mi-perfil') }}">Mi perfil</a>
              <a href="{{ url('/usuario/mis-pedidos') }}">Mis pedidos</a>
            @elseif(auth()->user()->rol_id === 2)
              {{-- Admin --}}
              <a href="{{ url('/gestion-usuarios') }}">Gestión usuarios</a>
              <a href="{{ url('/admin/inspiracion') }}">Gestión inspiración</a>
              <a href="{{ url('/admin/opciones') }}">Gestión personalización</a>
              <a href="{{ url('/admin/pedidos') }}">Gestión pedidos</a>
            @endif

            <a href="{{ url('/logout') }}">Cerrar sesión</a>
          </div>
        @else
          <a href="{{ url('/login') }}" class="btn-login">Iniciar sesión</a>
        @endauth
      </div>
    </div>
  </div>
</header>