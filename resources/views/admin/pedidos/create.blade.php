@extends('layouts.app')

@section('title', 'Crear Nuevo Pedido')

@section('content')
<div class="container py-5">
    <div class="row justify-content-center">
        <div class="col-lg-8">
            <div class="card border-0 shadow-sm animate-in">
                <div class="card-header bg-white py-3">
                    <div class="d-flex justify-content-between align-items-center">
                        <h5 class="mb-0 fw-bold"><i class="bi bi-bag-plus-fill me-2 text-primary"></i>Crear Nuevo Pedido</h5>
                        <a href="{{ route('admin.pedidos.index') }}" class="btn btn-sm btn-outline-secondary">
                            <i class="bi bi-x-lg"></i> Cancelar
                        </a>
                    </div>
                </div>
                <div class="card-body p-4">
                    <form action="{{ route('admin.pedidos.store') }}" method="POST">
                        @csrf
                        
                        {{-- Selección de Tipo de Cliente --}}
                        <div class="mb-4">
                            <label class="form-label fw-bold mb-3">¿A quién pertenece el pedido?</label>
                            <div class="d-flex gap-3">
                                <div class="form-check custom-radio-card flex-fill">
                                    <input class="form-check-input" type="radio" name="tipo_cliente" id="clienteRegistrado" value="registrado" checked onchange="toggleClienteFields()">
                                    <label class="form-check-label w-100 p-3 border rounded text-center cursor-pointer" for="clienteRegistrado">
                                        <i class="bi bi-person-check-fill d-block fs-4 mb-2 text-primary"></i>
                                        Usuario Registrado
                                    </label>
                                </div>
                                <div class="form-check custom-radio-card flex-fill">
                                    <input class="form-check-input" type="radio" name="tipo_cliente" id="clienteExterno" value="externo" onchange="toggleClienteFields()">
                                    <label class="form-check-label w-100 p-3 border rounded text-center cursor-pointer" for="clienteExterno">
                                        <i class="bi bi-person-rolodex d-block fs-4 mb-2 text-secondary"></i>
                                        Cliente Externo / Anónimo
                                    </label>
                                </div>
                            </div>
                        </div>

                        {{-- Campos Dinámicos --}}
                        <div id="campoRegistrado" class="mb-4">
                            <label class="form-label fw-bold">Seleccionar Usuario</label>
                            <select name="usuIdCliente" class="form-select" id="selectUsuario">
                                <option value="">-- Buscar usuario --</option>
                                @foreach($usuarios as $user)
                                    {{-- Filtramos para no mostrar administradores si no quieres --}}
                                    @if(isset($user['rol']) && $user['rol']['rolNombre'] !== 'ADMIN')
                                        <option value="{{ $user['usuId'] ?? $user['id'] }}">
                                            {{ $user['usuNombre'] ?? $user['nombre'] }} ({{ $user['usuCorreo'] ?? $user['correo'] }})
                                        </option>
                                    @endif
                                @endforeach
                            </select>
                            <div class="form-text">El pedido quedará vinculado al historial de este usuario.</div>
                        </div>

                        <div id="campoExterno" class="mb-4 d-none">
                            <label class="form-label fw-bold">Datos del Cliente Externo</label>
                            <div class="card bg-light border-0">
                                <div class="card-body">
                                    <div class="row g-3">
                                        <div class="col-md-7">
                                            <label class="form-label small text-muted fw-bold">Nombre Completo</label>
                                            <div class="input-group">
                                                <span class="input-group-text bg-white border-end-0"><i class="bi bi-person"></i></span>
                                                <input type="text" name="nombre_cliente_ext" class="form-control border-start-0" placeholder="Ej: Maria Gonzalez">
                                            </div>
                                        </div>
                                        <div class="col-md-5">
                                            <label class="form-label small text-muted fw-bold">Teléfono / WhatsApp</label>
                                            <div class="input-group">
                                                <span class="input-group-text bg-white border-end-0"><i class="bi bi-whatsapp"></i></span>
                                                <input type="text" name="telefono_cliente_ext" class="form-control border-start-0" placeholder="Ej: 3001234567">
                                            </div>
                                        </div>
                                    </div>
                                    <div class="form-text mt-2">
                                        <i class="bi bi-info-circle me-1"></i>
                                        El sistema guardará esto automáticamente como: <strong>"Nombre - Teléfono"</strong>.
                                    </div>
                                </div>
                            </div>
                        </div>

                        <hr>

                        {{-- Detalles del Pedido --}}
                        <div class="mb-4">
                            <label class="form-label fw-bold">Descripción / Requerimientos</label>
                            <textarea name="descripcion" class="form-control" rows="4" placeholder="Describe qué joya desea el cliente (Material, talla, piedras, etc)..." required></textarea>
                        </div>

                        <div class="d-grid">
                            <button type="submit" class="btn btn-primary py-2 fw-bold">
                                <i class="bi bi-save me-2"></i>Guardar Pedido
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
    function toggleClienteFields() {
        const isRegistrado = document.getElementById('clienteRegistrado').checked;
        const campoReg = document.getElementById('campoRegistrado');
        const campoExt = document.getElementById('campoExterno');
        const selectUser = document.getElementById('selectUsuario');

        if (isRegistrado) {
            campoReg.classList.remove('d-none');
            campoExt.classList.add('d-none');
            selectUser.required = true;
        } else {
            campoReg.classList.add('d-none');
            campoExt.classList.remove('d-none');
            selectUser.required = false;
        }
    }
</script>

<style>
    .cursor-pointer { cursor: pointer; transition: all 0.2s; }
    .form-check-input:checked + .form-check-label {
        border-color: #0d6efd !important;
        background-color: #f0f8ff;
        color: #0d6efd;
    }
    /* Ocultar el radio button real para dejar solo las tarjetas visuales */
    .custom-radio-card .form-check-input { position: absolute; opacity: 0; }
</style>
@endpush
@endsection