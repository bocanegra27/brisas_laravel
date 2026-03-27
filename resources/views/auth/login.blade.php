@extends('layouts.app')

@section('title', 'Iniciar Sesion - Brisas Gems')

@push('styles')
<style>

    :root {
    --primary-color: #108174;
    
}
    body {
        background-color: #f8f9fa;
        background-image: radial-gradient(#0096881a 1px, transparent 1px);
        background-size: 20px 20px;
    }

    .login-wrapper {
        min-height: calc(100vh - 64px);
        display: flex;
        align-items: center;
        justify-content: center;
        padding: 2rem 1rem;
    }

    .login-card {
        background: rgba(255, 255, 255, 0.95);
        backdrop-filter: blur(10px);
        border: 1px solid rgba(255, 255, 255, 0.2);
        border-radius: 20px;
        box-shadow: 0 15px 35px rgba(0, 0, 0, 0.1);
        overflow: hidden;
        width: 100%;
        max-width: 400px;
        transition: transform 0.3s ease;
    }

    .login-header {
        background: linear-gradient(135deg, var(--primary-color), var(--primary-hover));
        padding: 30px 20px;
        text-align: center;
        color: black;
    }

    .login-header h2 {
        font-weight: 700;
        margin: 0;
        font-size: 1.8rem;
    }
    
    .login-header p {
        margin: 5px 0 0;
        opacity: 0.9;
        font-size: 0.9rem;
    }

    .login-header__logo-container {
    width: 100%;               /* Asegura que ocupe todo el ancho para centrar */
    display: flex;
    justify-content: center;
    margin-bottom: 15px;
    }

    .login-header__logo-img {
        height: 100px;              /* Ajusta el tamaño a tu gusto */
        width: auto;
        display: block;            /* Evita espacios fantasmas debajo de la imagen */
        margin: 0 auto;            /* Refuerzo de centrado */
    }

    .login-body {
        padding: 40px 30px;
    }

    .form-floating > label {
        color: #6c757d;
    }

    .form-control:focus {
        border-color: var(--primary-color);
        box-shadow: 0 0 0 0.25rem rgba(0, 150, 136, 0.25);
    }

    .btn-primary {
        background-color: transparent; /* Fondo transparente inicialmente */
        color: var(--primary-color);   /* Color de letra igual al del header */
        border: 2px solid var(--primary-color); /* Borde con el color primario */
        padding: 12px;
        font-weight: 600;
        border-radius: 10px;
        transition: all 0.3s ease;
        display: flex;
        align-items: center;
        justify-content: center;
    }

    .btn-primary:hover {
        background-color: var(--primary-color); /* Se llena de color al pasar el mouse */
        color: #ffffff;                         /* La letra cambia a blanco */
        transform: translateY(-2px);
        box-shadow: 0 5px 15px rgba(0, 150, 136, 0.3);
        border-color: var(--primary-color);
    }

    /* Para asegurar que el icono también cambie de color */
    .btn-primary i {
        transition: transform 0.3s ease;
    }

    .btn-primary:hover i {
        transform: translateX(3px); /* Efecto de movimiento en la flechita */
    }

    .password-toggle {
        cursor: pointer;
        color: #6c757d;
        z-index: 10;
    }

    .forgot-link {
        color: var(--primary-color);
        text-decoration: none;
        font-size: 0.9rem;
        transition: color 0.2s;
    }

    .forgot-link:hover {
        color: var(--primary-hover);
        text-decoration: underline;
    }

    .register-text {
        font-size: 0.9rem;
        text-align: center;
        margin-top: 20px;
        color: #6c757d;
    }
</style>
@endpush
@section('content')
<div class="login-wrapper">
    <div class="login-card">
        <div class="login-header">
            <div class="login-header__logo-container">
                <img src="{{ asset('assets/img/logo/logo_120.png') }}" 
                    alt="Brisas Gems Logo" 
                    class="login-header__logo-img">
            </div>
            {{-- Si quieres el nombre en texto, puedes descomentar la línea de abajo --}}
            <h2 class="mt-2">Brisas Gems</h2>
            <p>Bienvenido de nuevo</p>
        </div>

        <div class="login-body">
            @if (session('success'))
                <div class="alert alert-success alert-dismissible fade show" role="alert">
                    <small>{{ session('success') }}</small>
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            @endif

            @if ($errors->any())
                <div class="alert alert-danger alert-dismissible fade show" role="alert">
                    <ul class="mb-0 small ps-3">
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            @endif

            <form action="{{ route('login.handle') }}" method="POST">
                @csrf
                
                <div class="form-floating mb-3">
                    <input type="email" class="form-control" id="email" name="email" placeholder="nombre@ejemplo.com" value="{{ old('email') }}" required autofocus>
                    <label for="email"><i class="bi bi-envelope me-2"></i>Correo electrónico</label>
                </div>

                <div class="input-group mb-3 position-relative">
                    <div class="form-floating flex-grow-1">
                        <input type="password" class="form-control" id="password" name="password" placeholder="Contraseña" required>
                        <label for="password"><i class="bi bi-lock me-2"></i>Contraseña</label>
                    </div>
                    <span class="input-group-text bg-white border-start-0" id="togglePassword" style="cursor: pointer;">
                        <i class="bi bi-eye-slash" id="eyeIcon"></i>
                    </span>
                </div>

                {{-- 
                <div class="d-flex justify-content-end mb-4">
                    <a href="{{ route('password.request') }}" class="forgot-link">¿Olvidaste tu contraseña?</a>
                </div> 
                --}}

                <div class="d-grid">
                    <button type="submit" class="btn btn-primary btn-lg">
                        Ingresar <i class="bi bi-box-arrow-in-right ms-2"></i>
                    </button>
                </div>
            </form>

            <div class="register-text">
                ¿No tienes una cuenta? <a href="{{ route('register.show') }}" class="forgot-link fw-bold">Regístrate aquí</a>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
    const togglePassword = document.querySelector('#togglePassword');
    const password = document.querySelector('#password');
    const eyeIcon = document.querySelector('#eyeIcon');

    togglePassword.addEventListener('click', function (e) {
        const type = password.getAttribute('type') === 'password' ? 'text' : 'password';
        password.setAttribute('type', type);

        if (type === 'text') {
            eyeIcon.classList.remove('bi-eye-slash');
            eyeIcon.classList.add('bi-eye');
        } else {
            eyeIcon.classList.remove('bi-eye');
            eyeIcon.classList.add('bi-eye-slash');
        }
    });
</script>
@endpush