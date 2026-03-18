/**
 *  MÓDULO DE MENSAJES MEJORADO - BRISAS GEMS
 * JavaScript con nuevas funcionalidades:
 * - Ver personalización inline
 * - Crear pedido desde mensaje
 * - Modal simplificado de detalles
 */

// ============================================
//  VER DETALLE MEJORADO (CON PERSONALIZACIÓN)
// ============================================
async function verDetalleMejorado(mensajeId, tienePersonalizacion) {
    try {
        // Mostrar loading
        const modal = new bootstrap.Modal(document.getElementById('modalDetalleMejorado'));
        const contenido = document.getElementById('modalDetalleContenido');
        
        contenido.innerHTML = `
            <div class="text-center py-5">
                <div class="spinner-border text-primary" role="status">
                    <span class="visually-hidden">Cargando...</span>
                </div>
                <p class="text-muted mt-3">Cargando detalles...</p>
            </div>
        `;
        
        modal.show();
        
        // Llamar al endpoint correcto según tenga o no personalización
        const endpoint = tienePersonalizacion 
            ? `/admin/mensajes/${mensajeId}/con-personalizacion`
            : `/admin/mensajes/${mensajeId}`;
        
        const response = await fetch(endpoint);
        const data = await response.json();
        
        if (!data.success) {
            throw new Error(data.message || 'Error al cargar el mensaje');
        }
        
        // Renderizar contenido
        const mensaje = tienePersonalizacion ? data.contacto : data.mensaje;
        const personalizacion = tienePersonalizacion ? data.personalizacion : null;
        
        contenido.innerHTML = generarHTMLDetalle(mensaje, personalizacion);
        
    } catch (error) {
        console.error('Error al ver detalle:', error);
        
        Swal.fire({
            title: 'Error',
            text: error.message || 'No se pudo cargar el detalle del mensaje.',
            icon: 'error',
            iconColor: '#ef4444',
            confirmButtonColor: '#009688'
        });
    }
}

/**
 * Genera HTML para el detalle del mensaje
 */
function generarHTMLDetalle(mensaje, personalizacion) {
    const tipoClienteBadge = obtenerBadgeTipoCliente(mensaje.tipoCliente);
    const estadoBadge = obtenerBadgeEstado(mensaje.estado);
    
    let html = `
        <div class="detalle-mensaje-mejorado">
            <div class="d-flex justify-content-between align-items-start mb-4 flex-wrap gap-2">
                <div>
                    <h6 class="text-muted mb-2">ID: #${mensaje.id}</h6>
                    <small class="text-muted">
                        ${new Date(mensaje.fechaEnvio).toLocaleString('es-CO')}
                    </small>
                </div>
                <div class="d-flex gap-2 flex-wrap">
                    ${tipoClienteBadge}
                    ${estadoBadge}
                    ${mensaje.tienePersonalizacion ? '<span class="badge bg-info"><i class="bi bi-gem me-1"></i> Con diseño</span>' : ''}
                </div>
            </div>
            
            <div class="info-section mb-4">
                <h6 class="fw-bold mb-3">
                    <i class="bi bi-person-fill me-2 text-primary"></i>Remitente
                </h6>
                <div class="row g-3">
                    <div class="col-md-6">
                        <label class="text-muted small">Nombre:</label>
                        <p class="fw-medium mb-0">${mensaje.nombre}</p>
                    </div>
                    <div class="col-md-6">
                        <label class="text-muted small">Correo:</label>
                        <p class="fw-medium mb-0">${mensaje.correo}</p>
                    </div>
                    <div class="col-md-6">
                        <label class="text-muted small">Teléfono:</label>
                        <p class="fw-medium mb-0">${mensaje.telefono}</p>
                    </div>
                    <div class="col-md-6">
                        <label class="text-muted small">Vía de contacto:</label>
                        <p class="fw-medium mb-0">
                            ${mensaje.via === 'formulario' 
                                ? '<i class="bi bi-envelope-fill me-1"></i> Formulario' 
                                : '<i class="bi bi-whatsapp me-1"></i> WhatsApp'}
                        </p>
                    </div>
                </div>
            </div>
            
            <div class="info-section mb-4">
                <h6 class="fw-bold mb-3">
                    <i class="bi bi-chat-left-text-fill me-2 text-primary"></i>Mensaje
                </h6>
                <div class="mensaje-box p-3 bg-light rounded">
                    ${mensaje.mensaje}
                </div>
            </div>
    `;
    
    if (personalizacion) {
        html += generarHTMLPersonalizacion(personalizacion);
    }
    
    html += `
            <div class="d-flex justify-content-end gap-2 flex-wrap mt-3">
                <button class="btn btn-secondary" data-bs-dismiss="modal">
                    <i class="bi bi-x-circle me-2"></i>Cerrar
                </button>
                <button class="btn btn-primary" onclick="editarEstadoRapido(${mensaje.id})">
                    <i class="bi bi-pencil-fill me-2"></i>Editar Estado
                </button>
                <button class="btn btn-success" onclick="crearPedidoDesdeMensaje(${mensaje.id}, '${mensaje.nombre.replace(/'/g, "\\'")}', ${mensaje.tienePersonalizacion})">
                    <i class="bi bi-cart-plus-fill me-2"></i>Crear Pedido
                </button>
            </div>
        </div>
    `;
    
    return html;
}

/**
 * Genera HTML para la sección de personalización
 */
function generarHTMLPersonalizacion(personalizacion) {
    let html = `
        <div class="info-section mb-4 personalizacion-section">
            <h6 class="fw-bold mb-3">
                <i class="bi bi-gem me-2 text-primary"></i>Personalización Vinculada
            </h6>
            <div class="personalizacion-card p-3 bg-light rounded">
                <div class="row g-3">
                    <div class="col-md-6">
                        <label class="text-muted small">ID Personalización:</label>
                        <p class="fw-medium mb-0">#${personalizacion.id}</p>
                    </div>
                    <div class="col-md-6">
                        <label class="text-muted small">Fecha:</label>
                        <p class="fw-medium mb-0">${new Date(personalizacion.fecha).toLocaleString('es-CO')}</p>
                    </div>
                </div>
                
                <hr class="my-3">
                
                <h6 class="fw-bold mb-3">Configuración del Diseño:</h6>
                <div class="row g-2">
    `;
    
    // Mostrar cada detalle de la personalización
    if (personalizacion.detalles && personalizacion.detalles.length > 0) {
        personalizacion.detalles.forEach(detalle => {
            
            const nombreVariable = detalle.opcionNombre || 'Campo Desconocido';  
            const valorSeleccionado = detalle.valorNombre || 'Sin valor';        
            
            
            html += `
                <div class="col-md-6">
                    <div class="detalle-personalizacion p-2 bg-white rounded">
                        <small class="text-muted d-block">${nombreVariable}:</small>
                        <strong>${valorSeleccionado}</strong>
                    </div>
                </div>
            `;
        });
    } else {
        html += '<p class="text-muted">Sin detalles disponibles</p>';
        console.warn('Personalización sin detalles:', personalizacion);
    }
    
    html += `
                </div>
            </div>
        </div>
    `;
    
    return html;
}

// ============================================
// 🔥 CREAR PEDIDO DESDE MENSAJE (Versión FINAL con ARCHIVO)
// ============================================
async function crearPedidoDesdeMensaje(mensajeId, nombreCliente, tienePersonalizacion, personalizacionId) {
    try {
        const resultado = await Swal.fire({
            title: '🎁 Crear Pedido',
            html: `
                <p class="mb-3">¿Deseas crear un pedido para <strong>${nombreCliente}</strong>?</p>
                ${tienePersonalizacion 
                    ? '<div class="alert alert-info"><i class="bi bi-gem me-2"></i>Este mensaje tiene una personalización vinculada que se asociará al pedido.</div>' 
                    : '<div class="alert alert-warning"><i class="bi bi-exclamation-triangle me-2"></i>Este mensaje NO tiene personalización vinculada. El pedido se creará sin diseño previo.</div>'
                }
                <div class="mt-3">
                    <label class="form-label">Comentarios iniciales (opcional):</label>
                    <textarea id="pedidoComentarios" class="form-control" rows="3" placeholder="Ej: Cliente solicitó entrega urgente..."></textarea>
                </div>
            `,
            icon: 'question',
            showCancelButton: true,
            confirmButtonText: '<i class="bi bi-check-circle me-2"></i>Crear Pedido',
            cancelButtonText: '<i class="bi bi-x-circle me-2"></i>Cancelar',
            confirmButtonColor: '#009688',
            cancelButtonColor: '#6b7280',
            customClass: {
                popup: 'swal-wide'
            },
            preConfirm: () => {
                return document.getElementById('pedidoComentarios').value;
            }
        });
        
        if (!resultado.isConfirmed) return;
        
        const comentarios = resultado.value;
        
        // Mostrar loading
        Swal.fire({
            title: 'Creando pedido...',
            text: 'Por favor espera',
            allowOutsideClick: false,
            allowEscapeKey: false,
            showConfirmButton: false,
            didOpen: () => Swal.showLoading()
        });
        
        // --- 1. LLAMADA PARA CREAR EL PEDIDO ---
        const response = await fetch(`/admin/pedidos/desde-mensaje/${mensajeId}`, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content || ''
            },
            body: JSON.stringify({
                comentarios: comentarios || null,
                personalizacionId: personalizacionId || null
            })
        });
        
        const data = await response.json();
        
        if (!data.success) {
            throw new Error(data.message || 'Error al crear el pedido');
        }

        // --- 2. LLAMADA PARA ARCHIVAR EL MENSAJE  ---
        // Se ejecuta si el pedido fue creado con éxito
        const estadoArchiveResponse = await fetch(`/admin/mensajes/${mensajeId}/estado`, {
            method: 'PATCH',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content || ''
            },
            body: JSON.stringify({ estado: 'archivado' }) // <-- Cambiar estado a 'archivado'
        });

        // No verificamos si el archivado falla, solo si la llamada al backend devuelve 2xx
        if (!estadoArchiveResponse.ok) {
            console.warn('Advertencia: El pedido fue creado, pero falló la solicitud de archivado del mensaje.');
            // Continuamos, ya que el pedido es lo importante.
        }
        
        // --- 3. MOSTRAR ÉXITO Y REDIRIGIR/RECARGAR ---
        
        Swal.fire({
            title: '¡Pedido Creado!',
            html: `
                <p class="mb-3">Pedido <strong>${data.pedido.pedCodigo}</strong> creado exitosamente.</p>
                <p class="text-muted">Estado inicial: ${data.pedido.estadoNombre}</p>
                <p class="text-success fw-bold"><i class="bi bi-check-circle-fill me-1"></i> Mensaje archivado.</p>
            `, // ➡️ Mensaje actualizado para reflejar el archivado
            icon: 'success',
            iconColor: '#22c55e',
            confirmButtonColor: '#009688',
            confirmButtonText: 'Ver Pedidos'
        }).then((result) => {
            if (result.isConfirmed) {
                // Si hace clic en "Ver Pedidos"
                window.location.href = '/admin/pedidos';
            } else {
                // Si hace clic en otro lado o simplemente cierra el modal, 
                // forzamos la recarga para que el mensaje archivado desaparezca de la lista.
                window.location.reload(); 
            }
        });
        
    } catch (error) {
        console.error('Error al crear pedido:', error);
        
        Swal.fire({
            title: 'Error',
            text: error.message || 'No se pudo crear el pedido.',
            icon: 'error',
            iconColor: '#ef4444',
            confirmButtonColor: '#ef4444'
        });
    }
}

// ============================================
// EDITAR ESTADO RÁPIDO (INLINE)
// ============================================
async function editarEstadoRapido(mensajeId) {
    try {
        const resultado = await Swal.fire({
            title: 'Cambiar Estado',
            input: 'select',
            inputOptions: {
                'pendiente': 'Pendiente',
                'atendido': 'Atendido',
                'archivado': 'Archivado'
            },
            inputPlaceholder: 'Selecciona un estado',
            showCancelButton: true,
            confirmButtonText: 'Guardar',
            cancelButtonText: 'Cancelar',
            confirmButtonColor: '#009688',
            inputValidator: (value) => {
                if (!value) {
                    return 'Debes seleccionar un estado';
                }
            }
        });
        
        if (!resultado.isConfirmed) return;
        
        const nuevoEstado = resultado.value;
        
        // Llamar al endpoint
        const response = await fetch(`/admin/mensajes/${mensajeId}/estado`, {
            method: 'PATCH',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content || ''
            },
            body: JSON.stringify({ estado: nuevoEstado })
        });
        
        const data = await response.json();
        
        if (!data.success) {
            throw new Error(data.message || 'Error al cambiar el estado');
        }
        
        Swal.fire({
            title: '¡Estado Actualizado!',
            text: data.message,
            icon: 'success',
            iconColor: '#22c55e',
            confirmButtonColor: '#009688',
            timer: 2000
        }).then(() => {
            window.location.reload();
        });
        
    } catch (error) {
        console.error('Error al editar estado:', error);
        
        Swal.fire({
            title: 'Error',
            text: error.message || 'No se pudo actualizar el estado.',
            icon: 'error',
            iconColor: '#ef4444',
            confirmButtonColor: '#ef4444'
        });
    }
}

// ============================================
// CAMBIAR ESTADO RÁPIDO (DESDE TABLA)
// ============================================
async function cambiarEstadoRapido(mensajeId, estadoActual) {
    // Determinar siguiente estado lógico
    const siguienteEstado = estadoActual === 'pendiente' ? 'atendido' 
                          : estadoActual === 'atendido' ? 'archivado' 
                          : 'pendiente';
    
    const resultado = await Swal.fire({
        title: '¿Cambiar Estado?',
        text: `Cambiar de "${estadoActual}" a "${siguienteEstado}"`,
        icon: 'question',
        showCancelButton: true,
        confirmButtonText: 'Sí, cambiar',
        cancelButtonText: 'Cancelar',
        confirmButtonColor: '#009688'
    });
    
    if (!resultado.isConfirmed) return;
    
    try {
        const response = await fetch(`/admin/mensajes/${mensajeId}/estado`, {
            method: 'PATCH',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content || ''
            },
            body: JSON.stringify({ estado: siguienteEstado })
        });
        
        const data = await response.json();
        
        if (!data.success) {
            throw new Error(data.message || 'Error al cambiar el estado');
        }
        
        Swal.fire({
            title: '¡Estado Actualizado!',
            icon: 'success',
            timer: 1500,
            showConfirmButton: false
        }).then(() => {
            window.location.reload();
        });
        
    } catch (error) {
        console.error('Error al cambiar estado:', error);
        
        Swal.fire({
            title: 'Error',
            text: error.message,
            icon: 'error',
            iconColor: '#ef4444',
            confirmButtonColor: '#ef4444'
        });
    }
}

// ============================================
// ELIMINAR MENSAJE
// ============================================
async function eliminarMensaje(mensajeId, nombreCliente) {
    const resultado = await Swal.fire({
        title: '⚠️ ¿Eliminar Mensaje?',
        html: `
            <p class="mb-3">Estás a punto de eliminar el mensaje de:</p>
            <p class="fw-bold mb-3">"${nombreCliente}"</p>
            <p class="text-danger mb-0">Esta acción no se puede deshacer.</p>
        `,
        icon: 'warning',
        iconColor: '#ef4444',
        showCancelButton: true,
        confirmButtonText: 'Sí, eliminar',
        cancelButtonText: 'Cancelar',
        confirmButtonColor: '#ef4444',
        cancelButtonColor: '#6b7280'
    });
    
    if (!resultado.isConfirmed) return;
    
    try {
        Swal.fire({
            title: 'Eliminando...',
            allowOutsideClick: false,
            showConfirmButton: false,
            didOpen: () => Swal.showLoading()
        });
        
        const response = await fetch(`/admin/mensajes/${mensajeId}`, {
            method: 'DELETE',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content || ''
            }
        });
        
        const data = await response.json();
        
        if (!data.success) {
            throw new Error(data.message || 'Error al eliminar el mensaje');
        }
        
        Swal.fire({
            title: '¡Eliminado!',
            text: data.message,
            icon: 'success',
            iconColor: '#22c55e',
            confirmButtonColor: '#009688',
            timer: 2000
        }).then(() => {
            window.location.reload();
        });
        
    } catch (error) {
        console.error('Error al eliminar mensaje:', error);
        
        Swal.fire({
            title: 'Error',
            text: error.message,
            icon: 'error',
            iconColor: '#ef4444',
            confirmButtonColor: '#ef4444'
        });
    }
}

// ============================================
// HELPERS
// ============================================
function obtenerBadgeTipoCliente(tipo) {
    const badges = {
        'registrado': '<span class="badge bg-success"><i class="bi bi-person-check-fill me-1"></i> Registrado</span>',
        'anonimo': '<span class="badge bg-secondary"><i class="bi bi-incognito me-1"></i> Anónimo</span>'
    };
    
    return badges[tipo] || badges['anonimo'];
}

function obtenerBadgeEstado(estado) {
    const badges = {
        'pendiente': '<span class="badge bg-warning"><i class="bi bi-clock-fill me-1"></i> Pendiente</span>',
        'atendido': '<span class="badge bg-success"><i class="bi bi-check-circle-fill me-1"></i> Atendido</span>',
        'archivado': '<span class="badge bg-secondary"><i class="bi bi-archive-fill me-1"></i> Archivado</span>'
    };
    
    return badges[estado] || '';
}

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
        });
    }
});