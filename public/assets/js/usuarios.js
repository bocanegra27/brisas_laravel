/**
 * MÓDULO DE GESTIÓN DE USUARIOS - BRISAS GEMS
 * JavaScript para interactividad, búsqueda, filtros y acciones CRUD
 */

// ============================================
// BÚSQUEDA EN TIEMPO REAL
// ============================================
document.addEventListener('DOMContentLoaded', function() {
    const searchInput = document.getElementById('searchInput');
    
    if (searchInput) {
        searchInput.addEventListener('input', function() {
            const searchTerm = this.value.toLowerCase().trim();
            const rows = document.querySelectorAll('.usuario-row');
            
            rows.forEach(row => {
                const nombre = row.getAttribute('data-nombre');
                const correo = row.getAttribute('data-correo');
                
                if (nombre.includes(searchTerm) || correo.includes(searchTerm)) {
                    row.style.display = '';
                } else {
                    row.style.display = 'none';
                }
            });
            
            // Verificar si hay resultados
            checkEmptyResults();
        });
    }
});

// ============================================
// FILTROS (ROL Y ESTADO)
// ============================================
document.addEventListener('DOMContentLoaded', function() {
    const filterRol = document.getElementById('filterRol');
    const filterEstado = document.getElementById('filterEstado');
    
    if (filterRol) {
        filterRol.addEventListener('change', applyFilters);
    }
    
    if (filterEstado) {
        filterEstado.addEventListener('change', applyFilters);
    }
});

function applyFilters() {
    // Obtener valores de los filtros
    const rolId = document.getElementById('filterRol')?.value || '';
    const activo = document.getElementById('filterEstado')?.value || '';
    
    // Construir URL con parámetros
    const url = new URL(window.location.href);
    url.searchParams.set('page', '0'); // Reset página
    
    if (rolId) {
        url.searchParams.set('rolId', rolId);
    } else {
        url.searchParams.delete('rolId');
    }
    
    if (activo) {
        url.searchParams.set('activo', activo);
    } else {
        url.searchParams.delete('activo');
    }
    
    // Redirigir con filtros
    window.location.href = url.toString();
}

// ============================================
// CAMBIO DE TAMAÑO DE PÁGINA
// ============================================
document.addEventListener('DOMContentLoaded', function() {
    const pageSize = document.getElementById('pageSize');
    
    if (pageSize) {
        pageSize.addEventListener('change', function() {
            const url = new URL(window.location.href);
            url.searchParams.set('size', this.value);
            url.searchParams.set('page', '0'); // Reset a primera página
            window.location.href = url.toString();
        });
    }
});

// ============================================
// TOGGLE ESTADO (ACTIVAR/DESACTIVAR)
// ============================================
function toggleEstado(usuarioId, nuevoEstado) {
    const accion = nuevoEstado ? 'activar' : 'desactivar';
    const titulo = nuevoEstado ? '¿Activar usuario?' : '¿Desactivar usuario?';
    const texto = nuevoEstado 
        ? 'El usuario podrá acceder al sistema nuevamente.' 
        : 'El usuario no podrá acceder al sistema hasta que sea activado.';
    const confirmText = nuevoEstado ? 'Sí, activar' : 'Sí, desactivar';
    const iconColor = nuevoEstado ? '#22c55e' : '#f59e0b';
    
    Swal.fire({
        title: titulo,
        text: texto,
        icon: 'question',
        iconColor: iconColor,
        showCancelButton: true,
        confirmButtonColor: iconColor,
        cancelButtonColor: '#6b7280',
        confirmButtonText: confirmText,
        cancelButtonText: 'Cancelar',
        reverseButtons: true,
        customClass: {
            popup: 'swal-custom',
            confirmButton: 'swal-btn-confirm',
            cancelButton: 'swal-btn-cancel'
        }
    }).then((result) => {
        if (result.isConfirmed) {
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
            
            // Realizar petición AJAX
            fetch(`/usuarios/${usuarioId}/toggle-activo`, {
                method: 'PATCH',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content || ''
                },
                body: JSON.stringify({
                    activo: nuevoEstado
                })
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    Swal.fire({
                        title: '¡Éxito!',
                        text: data.message,
                        icon: 'success',
                        iconColor: '#22c55e',
                        confirmButtonColor: '#009688',
                        confirmButtonText: 'Entendido'
                    }).then(() => {
                        // Recargar la página para actualizar el estado
                        window.location.reload();
                    });
                } else {
                    throw new Error(data.message || 'Error al cambiar el estado');
                }
            })
            .catch(error => {
                Swal.fire({
                    title: 'Error',
                    text: error.message || 'Ocurrió un error al cambiar el estado del usuario.',
                    icon: 'error',
                    iconColor: '#ef4444',
                    confirmButtonColor: '#ef4444',
                    confirmButtonText: 'Cerrar'
                });
            });
        }
    });
}

// ============================================
// ELIMINAR USUARIO
// ============================================
function eliminarUsuario(usuarioId, nombreUsuario) {
    Swal.fire({
        title: '⚠️ ¡Acción irreversible!',
        html: `
            <p class="mb-3">Estás a punto de eliminar permanentemente al usuario:</p>
            <p class="fw-bold mb-3">"${nombreUsuario}"</p>
            <p class="text-danger mb-0">Esta acción no se puede deshacer.</p>
        `,
        icon: 'warning',
        iconColor: '#ef4444',
        showCancelButton: true,
        confirmButtonColor: '#ef4444',
        cancelButtonColor: '#6b7280',
        confirmButtonText: 'Sí, eliminar permanentemente',
        cancelButtonText: 'Cancelar',
        reverseButtons: true,
        customClass: {
            popup: 'swal-custom',
            confirmButton: 'swal-btn-danger',
            cancelButton: 'swal-btn-cancel'
        }
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
            
            // Realizar petición AJAX
            fetch(`/usuarios/${usuarioId}`, {
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
                        title: '¡Eliminado!',
                        text: data.message,
                        icon: 'success',
                        iconColor: '#22c55e',
                        confirmButtonColor: '#009688',
                        confirmButtonText: 'Entendido'
                    }).then(() => {
                        // Recargar la página para actualizar la lista
                        window.location.reload();
                    });
                } else {
                    throw new Error(data.message || 'Error al eliminar el usuario');
                }
            })
            .catch(error => {
                Swal.fire({
                    title: 'Error',
                    text: error.message || 'Ocurrió un error al eliminar el usuario.',
                    icon: 'error',
                    iconColor: '#ef4444',
                    confirmButtonColor: '#ef4444',
                    confirmButtonText: 'Cerrar'
                });
            });
        }
    });
}

// ============================================
// VERIFICAR RESULTADOS VACÍOS
// ============================================
function checkEmptyResults() {
    const rows = document.querySelectorAll('.usuario-row');
    const visibleRows = Array.from(rows).filter(row => row.style.display !== 'none');
    const tbody = document.getElementById('usuariosTableBody');
    
    // Eliminar mensaje anterior si existe
    const emptyMessage = tbody.querySelector('.empty-search-message');
    if (emptyMessage) {
        emptyMessage.remove();
    }
    
    // Si no hay resultados visibles, mostrar mensaje
    if (visibleRows.length === 0) {
        const tr = document.createElement('tr');
        tr.className = 'empty-search-message';
        tr.innerHTML = `
            <td colspan="9" class="text-center py-5">
                <i class="bi bi-search display-4 text-muted d-block mb-3"></i>
                <p class="text-muted mb-0">No se encontraron usuarios que coincidan con tu búsqueda</p>
            </td>
        `;
        tbody.appendChild(tr);
    }
}

// ============================================
// VALIDACIÓN DE FORMULARIO CREAR
// ============================================
document.addEventListener('DOMContentLoaded', function() {
    const formCrear = document.getElementById('formCrearUsuario');
    
    if (formCrear) {
        formCrear.addEventListener('submit', function(e) {
            // Validar que las contraseñas coincidan
            const password = document.getElementById('password')?.value || '';
            const confirmation = document.getElementById('password_confirmation')?.value || '';
            
            if (password !== confirmation) {
                e.preventDefault();
                
                Swal.fire({
                    title: 'Contraseñas no coinciden',
                    text: 'Por favor, verifica que ambas contraseñas sean iguales.',
                    icon: 'warning',
                    iconColor: '#f59e0b',
                    confirmButtonColor: '#009688',
                    confirmButtonText: 'Entendido'
                });
                
                document.getElementById('password_confirmation').focus();
                return false;
            }
        });
    }
});

// ============================================
// ESTILOS PERSONALIZADOS PARA SWEETALERT2
// ============================================
const swalCustomStyles = `
    <style>
        .swal-custom {
            border-radius: 20px !important;
            font-family: 'Inter', sans-serif !important;
        }
        
        .swal-custom .swal2-title {
            font-family: 'Playfair Display', serif !important;
            font-weight: 700 !important;
            font-size: 1.75rem !important;
            color: #1e293b !important;
        }
        
        .swal-custom .swal2-html-container {
            font-size: 1rem !important;
            color: #64748b !important;
        }
        
        .swal-btn-confirm {
            border-radius: 12px !important;
            padding: 0.75rem 2rem !important;
            font-weight: 600 !important;
            transition: all 0.3s ease !important;
        }
        
        .swal-btn-confirm:hover {
            transform: translateY(-2px) !important;
            box-shadow: 0 4px 12px rgba(0,0,0,0.2) !important;
        }
        
        .swal-btn-cancel {
            border-radius: 12px !important;
            padding: 0.75rem 2rem !important;
            font-weight: 600 !important;
        }
        
        .swal-btn-danger {
            background: linear-gradient(135deg, #ef4444 0%, #dc2626 100%) !important;
            border: none !important;
        }
    </style>
`;

// Inyectar estilos personalizados
if (typeof Swal !== 'undefined') {
    document.head.insertAdjacentHTML('beforeend', swalCustomStyles);
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
        }, 5000); // 5 segundos
    });
});