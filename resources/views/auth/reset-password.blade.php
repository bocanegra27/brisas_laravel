<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Nueva Contraseña - Brisas Gems</title>
    <link rel="icon" href="{{ asset('assets/img/icons/icono.png') }}" />
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <link rel="stylesheet" href="{{ asset('assets/css/bootstrap.min.css') }}" />
    
    <style>
        :root { --primary-color: #009688; --primary-hover: #00796b; --bg-color: #f8f9fa; }
        body {
            background-color: var(--bg-color);
            background-image: radial-gradient(#0096881a 1px, transparent 1px);
            background-size: 20px 20px;
            font-family: 'Segoe UI', sans-serif;
            height: 100vh;
            display: flex; align-items: center; justify-content: center;
        }
        .login-card {
            background: rgba(255, 255, 255, 0.95);
            backdrop-filter: blur(10px);
            border-radius: 20px;
            box-shadow: 0 15px 35px rgba(0, 0, 0, 0.1);
            width: 100%; max-width: 400px; overflow: hidden;
        }
        .login-header {
            background: linear-gradient(135deg, var(--primary-color), var(--primary-hover));
            padding: 30px 20px; text-align: center; color: white;
        }
        .login-body { padding: 40px 30px; }
        .btn-primary {
            background-color: var(--primary-color); border: none; padding: 12px;
            font-weight: 600; border-radius: 10px; width: 100%;
        }
        .btn-primary:hover { background-color: var(--primary-hover); }
    </style>
</head>
<body>
    <div class="login-card">
        <div class="login-header">
            <i class="bi bi-key mb-2" style="font-size: 2rem;"></i>
            <h3 class="m-0 fw-bold">Nueva Contraseña</h3>
        </div>

        <div class="login-body">
            @if ($errors->any())
                <div class="alert alert-danger alert-dismissible fade show" role="alert">
                    <ul class="mb-0 small ps-3">
                        @foreach ($errors->all() as $error) <li>{{ $error }}</li> @endforeach
                    </ul>
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            @endif

            <form action="{{ route('password.update') }}" method="POST">
                @csrf
                {{-- Token oculto (vital para saber quién es) --}}
                <input type="hidden" name="token" value="{{ $token }}">

                <div class="mb-3">
                    <label class="form-label text-muted">Nueva Contraseña</label>
                    <input type="password" name="password" class="form-control" required minlength="8" placeholder="Mínimo 8 caracteres">
                </div>

                <div class="mb-4">
                    <label class="form-label text-muted">Confirmar Contraseña</label>
                    <input type="password" name="password_confirmation" class="form-control" required placeholder="Repite la contraseña">
                </div>

                <button type="submit" class="btn btn-primary">
                    Cambiar Contraseña <i class="bi bi-check-circle ms-2"></i>
                </button>
            </form>
        </div>
    </div>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>