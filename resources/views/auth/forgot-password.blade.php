<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Recuperar Contraseña - Brisas Gems</title>
    <link rel="icon" href="{{ asset('assets/img/icons/icono.png') }}" />
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <link rel="stylesheet" href="{{ asset('assets/css/bootstrap.min.css') }}" />
    
    <style>
        :root {
            --primary-color: #009688;
            --primary-hover: #00796b;
            --bg-color: #f8f9fa;
        }
        
        body {
            background-color: var(--bg-color);
            background-image: radial-gradient(#0096881a 1px, transparent 1px);
            background-size: 20px 20px;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
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
            color: white;
        }

        .login-header h2 {
            font-weight: 700;
            margin: 0;
            font-size: 1.5rem;
        }
        
        .login-header p {
            margin: 5px 0 0;
            opacity: 0.9;
            font-size: 0.9rem;
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
            background-color: var(--primary-color);
            border: none;
            padding: 12px;
            font-weight: 600;
            border-radius: 10px;
            transition: all 0.3s ease;
        }

        .btn-primary:hover {
            background-color: var(--primary-hover);
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(0, 150, 136, 0.3);
        }

        .back-link {
            color: #6c757d;
            text-decoration: none;
            font-size: 0.9rem;
            transition: color 0.2s;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .back-link:hover {
            color: var(--primary-color);
        }
    </style>
</head>
<body>

    <div class="login-card">
        <div class="login-header">
            <i class="bi bi-shield-lock mb-2" style="font-size: 2rem;"></i>
            <h2>Recuperación</h2>
            <p>Ingresa tu correo para restablecer tu contraseña</p>
        </div>

        <div class="login-body">
            {{-- Mensajes de Éxito o Error --}}
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

            <form action="{{ route('password.email') }}" method="POST">
                @csrf
                
                <div class="form-floating mb-4">
                    <input type="email" class="form-control" id="email" name="email" placeholder="nombre@ejemplo.com" required autofocus>
                    <label for="email"><i class="bi bi-envelope me-2"></i>Correo electrónico</label>
                </div>

                <div class="d-grid mb-4">
                    <button type="submit" class="btn btn-primary btn-lg">
                        Enviar Enlace <i class="bi bi-send ms-2"></i>
                    </button>
                </div>

                <div class="text-center">
                    <a href="{{ route('login') }}" class="back-link">
                        <i class="bi bi-arrow-left me-2"></i> Volver al Login
                    </a>
                </div>
            </form>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>