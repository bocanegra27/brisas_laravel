/**
 * MÓDULO DE GESTIÓN DE MENSAJES - BRISAS GEMS
 * JavaScript para interactividad, búsqueda, filtros y acciones CRUD
 */

// ============================================
// VARIABLES GLOBALES
// ============================================
let mensajeActual = null;

// ============================================
// BÚSQUEDA EN TIEMPO REAL
// ============================================
document.addEventListener('DOMContentLoaded', function() {
    const searchInput = document.getElementById('searchInput');
    
    if (searchInput) {
        searchInput.addEventListener('input', function() {
            const searchTerm = this.value.toLowerCase().trim();
            const rows = document.querySelectorAll('.mensaje-row');
            
            rows.forEach(row => {
                const nombre = row.getAttribute('data-nombre');
                const correo = row.getAttribute('data-correo');
                const mensaje = row.getAttribute('data-mensaje');
                
                if (nombre.includes(searchTerm) || correo.includes(searchTerm) || mensaje.includes(searchTerm)) {
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
// FILTROS (VÍA Y ESTADO)
// ============================================
document.addEventListener('DOMContentLoaded', function() {
    const filterVia = document.getElementById('filterVia');
    const filterEstado = document.getElementById('filterEstado');
    
    if (filterVia) {
        filterVia.addEventListener('change', applyFilters);
    }
    
    if (filterEstado) {
        filterEstado.addEventListener('change', applyFilters);
    }
});

function applyFilters() {
    // Obtener valores de los filtros
    const via = document.getElementById('filterVia')?.value || '';
    const estado = document.getElementById('filterEstado')?.value || '';
    
    // Construir URL con parámetros
    const url = new URL(window.location.href);
    
    if (via) {
        url.searchParams.set('via', via);
    } else {
        url.searchParams.delete('via');
    }
    
    if (estado) {
        url.searchParams.set('estado', estado);
    } else {
        url.searchParams.delete('estado');
    }
    
    // Redirigir con filtros
    window.location.href = url.toString();
}

// ============================================
// VER DETALLE DE MENSAJE
// ============================================
function verDetalle(mensajeId) {
    // Mostrar loading
    Swal.fire({
        title: 'Cargando...',
        text: 'Por favor espera',
        allowOutsideClick: false,
        allowEscapeKey: false,
        showConfirmButton: false,
        didOpen: () => {
            Swal.showLoading();
        }
    });
    
    // Realizar petición AJAX
    fetch(`/admin/mensajes/${mensajeId}`, {
        method: 'GET',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content || ''
        }
    })
    .then(response => response.json())
    .then(data => {
        Swal.close();
        
        if (data.success) {
            mensajeActual = data.mensaje;
            mostrarModalDetalle(data.mensaje);
        } else {
            throw new Error(data.message || 'Error al cargar el mensaje');
        }
    })
    .catch(error => {
        Swal.fire({
            title: 'Error',
            text: error.message || 'Ocurrió un error al cargar el mensaje.',
            icon: 'error',
            iconColor: '#ef4444',
            confirmButtonColor: '#ef4444',
            confirmButtonText: 'Cerrar'
        });
    });
}

function mostrarModalDetalle(mensaje) {
    // Llenar datos del modal
    document.getElementById('detalleId').textContent = `#${mensaje.id}`;
    document.getElementById('detalleNombre').textContent = mensaje.nombre;
    document.getElementById('detalleCorreo').textContent = mensaje.correo;
    document.getElementById('detalleTelefono').textContent = mensaje.telefono;
    document.getElementById('detalleMensaje').textContent = mensaje.mensaje;
    
    // Vía
    const viaHtml = mensaje.via === 'formulario' 
        ? '<span class="badge-via badge-via-formulario"><i class="bi bi-envelope-fill"></i> Formulario</span>'
        : '<span class="badge-via badge-via-whatsapp"><i class="bi bi-whatsapp"></i> WhatsApp</span>';
    document.getElementById('detalleVia').innerHTML = viaHtml;
    
    // Estado
    let estadoHtml = '';
    if (mensaje.estado === 'pendiente') {
        estadoHtml = '<span class="badge-estado badge-pendiente"><i class="bi bi-clock-fill"></i> Pendiente</span>';
    } else if (mensaje.estado === 'atendido') {
        estadoHtml = '<span class="badge-estado badge-atendido"><i class="bi bi-check-circle-fill"></i> Atendido</span>';
    } else {
        estadoHtml = '<span class="badge-estado badge-archivado"><i class="bi bi-archive-fill"></i> Archivado</span>';
    }
    document.getElementById('detalleEstado').innerHTML = estadoHtml;
    
    // Usuario asociado
    document.getElementById('detalleUsuario').textContent = mensaje.usuarioNombre || '—';
    
    // Admin que atendió
    document.getElementById('detalleAdmin').textContent = mensaje.usuarioAdminNombre || '—';
    
    // Notas
    if (mensaje.notas) {
        document.getElementById('detalleNotas').textContent = mensaje.notas;
    } else {
        document.getElementById('detalleNotas').innerHTML = '<em class="text-muted">Sin notas</em>';
    }
    
    // Fecha
    const fecha = new Date(mensaje.fechaEnvio);
    const fechaFormateada = fecha.toLocaleString('es-CO', {
        year: 'numeric',
        month: 'long',
        day: 'numeric',
        hour: '2-digit',
        minute: '2-digit'
    });
    document.getElementById('detalleFecha').textContent = fechaFormateada;
    document.getElementById('detalleFechaCompleta').textContent = fechaFormateada;
    
    // Términos
    document.getElementById('detalleTerminos').textContent = mensaje.terminos ? 'Sí' : 'No';
    
    // Mostrar modal
    const modal = new bootstrap.Modal(document.getElementById('modalDetalle'));
    modal.show();
}

// ============================================
// ABRIR EDICIÓN DESDE DETALLE
// ============================================
document.addEventListener('DOMContentLoaded', function() {
    const btnEditarDesdeDetalle = document.getElementById('btnEditarDesdeDetalle');
    
    if (btnEditarDesdeDetalle) {
        btnEditarDesdeDetalle.addEventListener('click', function() {
            // Cerrar modal de detalle
            const modalDetalle = bootstrap.Modal.getInstance(document.getElementById('modalDetalle'));
            if (modalDetalle) {
                modalDetalle.hide();
            }
            
            // Abrir modal de edición con los datos actuales
            if (mensajeActual) {
                mostrarModalEditar(mensajeActual);
            }
        });
    }
});

function mostrarModalEditar(mensaje) {
    // Llenar datos de solo lectura
    document.getElementById('editarMensajeId').value = mensaje.id;
    document.getElementById('editarNombreReadonly').textContent = mensaje.nombre;
    document.getElementById('editarCorreoReadonly').textContent = mensaje.correo;
    document.getElementById('editarTelefonoReadonly').textContent = mensaje.telefono;
    
    // Llenar campos editables
    document.getElementById('editarEstado').value = mensaje.estado;
    document.getElementById('editarVia').value = mensaje.via;
    document.getElementById('editarUsuarioId').value = mensaje.usuarioId || '';
    document.getElementById('editarUsuarioIdAdmin').value = mensaje.usuarioIdAdmin || '';
    document.getElementById('editarNotas').value = mensaje.notas || '';
    
    // Mostrar modal
    const modal = new bootstrap.Modal(document.getElementById('modalEditar'));
    modal.show();
}

// ============================================
// GUARDAR EDICIÓN
// ============================================
document.addEventListener('DOMContentLoaded', function() {
    const formEditar = document.getElementById('formEditarMensaje');
    
    if (formEditar) {
        formEditar.addEventListener('submit', function(e) {
            e.preventDefault();
            
            const mensajeId = document.getElementById('editarMensajeId').value;
            const estado = document.getElementById('editarEstado').value;
            const via = document.getElementById('editarVia').value;
            const usuarioId = document.getElementById('editarUsuarioId').value;
            const usuarioIdAdmin = document.getElementById('editarUsuarioIdAdmin').value;
            const notas = document.getElementById('editarNotas').value;
            
            // Preparar datos
            const data = {
                estado: estado,
                via: via,
                notas: notas || null,
                usuarioId: usuarioId ? parseInt(usuarioId) : null,
                usuarioIdAdmin: usuarioIdAdmin ? parseInt(usuarioIdAdmin) : null
            };
            
            // Mostrar loading
            Swal.fire({
                title: 'Guardando...',
                text: 'Por favor espera',
                allowOutsideClick: false,
                allowEscapeKey: false,
                showConfirmButton: false,
                didOpen: () => {
                    Swal.showLoading();
                }
            });
            
            // Realizar petición AJAX
            fetch(`/admin/mensajes/${mensajeId}`, {
                method: 'PUT',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content || ''
                },
                body: JSON.stringify(data)
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
                        // Cerrar modal
                        const modal = bootstrap.Modal.getInstance(document.getElementById('modalEditar'));
                        if (modal) {
                            modal.hide();
                        }
                        // Recargar página
                        window.location.reload();
                    });
                } else {
                    throw new Error(data.message || 'Error al actualizar el mensaje');
                }
            })
            .catch(error => {
                Swal.fire({
                    title: 'Error',
                    text: error.message || 'Ocurrió un error al actualizar el mensaje.',
                    icon: 'error',
                    iconColor: '#ef4444',
                    confirmButtonColor: '#ef4444',
                    confirmButtonText: 'Cerrar'
                });
            });
        });
    }
});

// ============================================
// CAMBIAR ESTADO RÁPIDO
// ============================================
function cambiarEstadoRapido(mensajeId, estadoActual) {
    // Determinar siguiente estado
    let nuevoEstado = '';
    let tituloModal = '';
    let textoModal = '';
    let iconColor = '';
    
    if (estadoActual === 'pendiente') {
        nuevoEstado = 'atendido';
        tituloModal = '¿Marcar como atendido?';
        textoModal = 'El mensaje se marcará como atendido.';
        iconColor = '#22c55e';
    } else if (estadoActual === 'atendido') {
        nuevoEstado = 'archivado';
        tituloModal = '¿Archivar mensaje?';
        textoModal = 'El mensaje se moverá a archivados.';
        iconColor = '#6b7280';
    } else {
        nuevoEstado = 'pendiente';
        tituloModal = '¿Restaurar mensaje?';
        textoModal = 'El mensaje volverá a estar pendiente.';
        iconColor = '#f59e0b';
    }
    
    Swal.fire({
        title: tituloModal,
        text: textoModal,
        icon: 'question',
        iconColor: iconColor,
        showCancelButton: true,
        confirmButtonColor: iconColor,
        cancelButtonColor: '#6b7280',
        confirmButtonText: 'Sí, cambiar',
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
            fetch(`/admin/mensajes/${mensajeId}/estado`, {
                method: 'PATCH',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content || ''
                },
                body: JSON.stringify({
                    estado: nuevoEstado
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
                    text: error.message || 'Ocurrió un error al cambiar el estado del mensaje.',
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
// ELIMINAR MENSAJE
// ============================================
function eliminarMensaje(mensajeId, nombreRemitente) {
    Swal.fire({
        title: '⚠️ ¡Acción irreversible!',
        html: `
            <p class="mb-3">Estás a punto de eliminar permanentemente el mensaje de:</p>
            <p class="fw-bold mb-3">"${nombreRemitente}"</p>
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
            fetch(`/admin/mensajes/${mensajeId}`, {
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
                    throw new Error(data.message || 'Error al eliminar el mensaje');
                }
            })
            .catch(error => {
                Swal.fire({
                    title: 'Error',
                    text: error.message || 'Ocurrió un error al eliminar el mensaje.',
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
    const rows = document.querySelectorAll('.mensaje-row');
    const visibleRows = Array.from(rows).filter(row => row.style.display !== 'none');
    const tbody = document.getElementById('mensajesTableBody');
    
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
                <p class="text-muted mb-0">No se encontraron mensajes que coincidan con tu búsqueda</p>
            </td>
        `;
        tbody.appendChild(tr);
    }
}

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