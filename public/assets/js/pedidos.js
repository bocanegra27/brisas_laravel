/**
 * MODULO DE GESTION DE PEDIDOS - BRISAS GEMS
 * JavaScript para interactividad, busqueda, filtros y acciones CRUD
 */

// ============================================
// CAMBIAR ESTADO DE PEDIDO
// ============================================
function cambiarEstadoPedido(pedidoId, estadoActual) {
    // Guardar ID y estado actual
    document.getElementById('pedidoIdEstado').value = pedidoId;
    document.getElementById('nuevoEstado').value = estadoActual;
    
    // Mostrar modal
    const modal = new bootstrap.Modal(document.getElementById('modalCambiarEstado'));
    modal.show();
}

function confirmarCambioEstado() {
    const pedidoId = document.getElementById('pedidoIdEstado').value;
    const estadoId = document.getElementById('nuevoEstado').value;
    
    if (!pedidoId || !estadoId) {
        Swal.fire({
            title: 'Error',
            text: 'Datos incompletos para cambiar el estado.',
            icon: 'error',
            confirmButtonColor: '#ef4444'
        });
        return;
    }
    
    // Mostrar loading
    Swal.fire({
        title: 'Procesando...',
        text: 'Por favor espera',
        allowOutsideClick: false,
        allowEscapeKey: false,
        showConfirmButton: false,
        didOpen: () => {
            Swal.showLoading();
        }
    });
    
    // Realizar peticion AJAX
    fetch(`/admin/pedidos/${pedidoId}/estado`, {
        method: 'PATCH',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content || ''
        },
        body: JSON.stringify({
            estadoId: parseInt(estadoId)
        })
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            Swal.fire({
                title: 'Exito',
                text: data.message,
                icon: 'success',
                confirmButtonColor: '#009688'
            }).then(() => {
                // Cerrar modal y recargar pagina
                bootstrap.Modal.getInstance(document.getElementById('modalCambiarEstado')).hide();
                window.location.reload();
            });
        } else {
            throw new Error(data.message || 'Error al cambiar el estado');
        }
    })
    .catch(error => {
        Swal.fire({
            title: 'Error',
            text: error.message || 'Ocurrio un error al cambiar el estado del pedido.',
            icon: 'error',
            confirmButtonColor: '#ef4444'
        });
    });
}

// ============================================
// VER PERSONALIZACION
// ============================================
function verPersonalizacion(personalizacionId) {
    // Mostrar loading
    Swal.fire({
        title: 'Cargando...',
        text: 'Obteniendo detalles de la personalizacion',
        allowOutsideClick: false,
        allowEscapeKey: false,
        showConfirmButton: false,
        didOpen: () => {
            Swal.showLoading();
        }
    });
    
    // Obtener detalles de personalizacion
    fetch(`/api/personalizaciones/${personalizacionId}/detalles`, {
        method: 'GET',
        headers: {
            'Authorization': 'Bearer ' + (localStorage.getItem('jwt_token') || sessionStorage.getItem('jwt_token') || '')
        }
    })
    .then(response => response.json())
    .then(data => {
        if (data && data.detalles) {
            mostrarDetallesPersonalizacion(data);
        } else {
            throw new Error('No se encontraron detalles de la personalizacion');
        }
    })
    .catch(error => {
        Swal.fire({
            title: 'Error',
            text: error.message || 'No se pudo cargar la personalizacion.',
            icon: 'error',
            confirmButtonColor: '#ef4444'
        });
    });
}

function mostrarDetallesPersonalizacion(personalizacion) {
    // Construir HTML con los detalles
    let detallesHtml = '<div class="personalizacion-detalles">';
    detallesHtml += '<div class="row g-3">';
    
    personalizacion.detalles.forEach(detalle => {
        detallesHtml += `
            <div class="col-md-6">
                <div class="detalle-item">
                    <strong>${detalle.valNombre}:</strong>
                    <span class="ms-2">${detalle.opcionNombre}</span>
                </div>
            </div>
        `;
    });
    
    detallesHtml += '</div></div>';
    
    Swal.fire({
        title: 'Personalizacion del Pedido',
        html: detallesHtml,
        icon: 'info',
        confirmButtonColor: '#009688',
        confirmButtonText: 'Cerrar',
        width: '600px'
    });
}

// ============================================
// EDITAR PEDIDO
// ============================================
function editarPedido(pedidoId) {
    Swal.fire({
        title: 'Editar Pedido',
        html: `
            <p>Redirigiendo a la pagina de edicion...</p>
            <div class="spinner-border text-primary mt-3" role="status">
                <span class="visually-hidden">Cargando...</span>
            </div>
        `,
        showConfirmButton: false,
        allowOutsideClick: false,
        didOpen: () => {
            setTimeout(() => {
                window.location.href = `/admin/pedidos/${pedidoId}/editar`;
            }, 1000);
        }
    });
}

// ============================================
// ELIMINAR PEDIDO
// ============================================
function eliminarPedido(pedidoId, codigoPedido) {
    Swal.fire({
        title: 'Accion irreversible',
        html: `
            <p class="mb-3">Estas a punto de eliminar permanentemente el pedido:</p>
            <p class="fw-bold mb-3">"${codigoPedido}"</p>
            <p class="text-danger mb-0">Esta accion no se puede deshacer.</p>
        `,
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#ef4444',
        cancelButtonColor: '#6b7280',
        confirmButtonText: 'Si, eliminar permanentemente',
        cancelButtonText: 'Cancelar',
        reverseButtons: true
    }).then((result) => {
        if (result.isConfirmed) {
            // Mostrar loading
            Swal.fire({
                title: 'Eliminando...',
                text: 'Por favor espera',
                allowOutsideClick: false,
                allowEscapeKey: false,
                showConfirmButton: false,
                didOpen: () => {
                    Swal.showLoading();
                }
            });
            
            // Realizar peticion AJAX
            fetch(`/admin/pedidos/${pedidoId}`, {
                method: 'DELETE',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content || ''
                }
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    Swal.fire({
                        title: 'Eliminado',
                        text: data.message,
                        icon: 'success',
                        confirmButtonColor: '#009688'
                    }).then(() => {
                        window.location.reload();
                    });
                } else {
                    throw new Error(data.message || 'Error al eliminar el pedido');
                }
            })
            .catch(error => {
                Swal.fire({
                    title: 'Error',
                    text: error.message || 'Ocurrio un error al eliminar el pedido.',
                    icon: 'error',
                    confirmButtonColor: '#ef4444'
                });
            });
        }
    });
}

// ============================================
// AUTO-DISMISS ALERTS
// ============================================
document.addEventListener('DOMContentLoaded', function() {
    const alerts = document.querySelectorAll('.alert:not(.alert-info)');
    
    alerts.forEach(alert => {
        setTimeout(() => {
            const bsAlert = new bootstrap.Alert(alert);
            bsAlert.close();
        }, 5000);
    });
});

// ============================================
// ESTILOS PERSONALIZADOS PARA SWEETALERT2
// ============================================
const swalCustomStyles = `
    <style>
        .swal2-popup {
            border-radius: 20px;
            font-family: 'Inter', sans-serif;
        }
        
        .swal2-title {
            font-family: 'Playfair Display', serif;
            font-weight: 700;
            font-size: 1.75rem;
            color: #1e293b;
        }
        
        .swal2-html-container {
            font-size: 1rem;
            color: #64748b;
        }
        
        .swal2-confirm {
            border-radius: 12px;
            padding: 0.75rem 2rem;
            font-weight: 600;
            transition: all 0.3s ease;
        }
        
        .swal2-confirm:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(0,0,0,0.2);
        }
        
        .swal2-cancel {
            border-radius: 12px;
            padding: 0.75rem 2rem;
            font-weight: 600;
        }
        
        .personalizacion-detalles {
            text-align: left;
            padding: 1rem;
        }
        
        .detalle-item {
            padding: 0.75rem;
            background: #f8fafc;
            border-radius: 8px;
            margin-bottom: 0.5rem;
        }
        
        .detalle-item strong {
            color: #1e293b;
        }
        
        .detalle-item span {
            color: #64748b;
        }
    </style>
`;

// Inyectar estilos personalizados
if (typeof Swal !== 'undefined') {
    document.head.insertAdjacentHTML('beforeend', swalCustomStyles);
}