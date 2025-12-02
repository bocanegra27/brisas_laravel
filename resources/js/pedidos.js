/**
 * 🟢 GESTOR DE PEDIDOS CON ACTUALIZACIÓN DINÁMICA
 * Versión estable: Asegura la subida de archivos (Method Spoofing) y actualiza la barra de progreso.
 */

document.addEventListener('DOMContentLoaded', function() {
    console.log('✅ Sistema de pedidos cargado correctamente');
    
    // Seleccionar TODOS los formularios de actualización de pedidos
    const formsActualizar = document.querySelectorAll('form[data-form-pedido-id]');
    
    console.log(`📋 Formularios encontrados: ${formsActualizar.length}`);
    
    formsActualizar.forEach(form => {
        form.addEventListener('submit', function(e) {
            e.preventDefault(); // Evitar el envío tradicional
            
            // 1. Obtener ID de forma segura desde el atributo data
            const pedidoId = form.dataset.formPedidoId; 
            
            console.log(`🚀 Intentando actualizar pedido ID: ${pedidoId}`);
            
            if (!pedidoId) {
                console.error('❌ Error crítico: No se encontró el ID del pedido (data-form-pedido-id).');
                mostrarAlerta('danger', 'Error interno: ID de pedido no identificado.');
                return;
            }

            // 2. PREPARAR DATOS (CRÍTICO PARA LA COMUNICACIÓN)
            const formData = new FormData(form);
            
            // CRÍTICO: Laravel/Java esperan POST para subir archivos, 
            // pero necesitamos '_method' = 'PUT' para enrutarlo al update().
            formData.append('_method', 'PUT'); 

            const action = form.getAttribute('action');
            
            // Obtener botón de submit para mostrar loading
            const btnSubmit = form.querySelector('button[type="submit"]');
            const textoOriginal = btnSubmit.innerHTML;
            
            // Deshabilitar botón y mostrar spinner
            btnSubmit.disabled = true;
            btnSubmit.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span>Guardando...';
            
            // 3. OBTENER CSRF TOKEN
            const csrfToken = document.querySelector('meta[name="csrf-token"]')?.content;
            
            if (!csrfToken) {
                console.error('❌ CSRF Token no encontrado.');
                mostrarAlerta('danger', 'Error de seguridad. Recarga la página.');
                restaurarBoton(btnSubmit, textoOriginal);
                return;
            }
            
            // 4. ENVIAR PETICIÓN AJAX
            fetch(action, {
                method: 'POST', // IMPORTANTE: Usamos POST para que viaje el archivo
                headers: {
                    'X-CSRF-TOKEN': csrfToken,
                    'Accept': 'application/json', // Forzamos respuesta JSON
                },
                body: formData // Enviamos el FormData que contiene el archivo y _method=PUT
            })
            .then(async response => {
                const contentType = response.headers.get("content-type");
                
                if (response.status === 422) {
                    const errorData = await response.json();
                    throw new Error(errorData.message || "Error de validación (422).");
                }

                if (!response.ok) {
                    // Capturará 400 Bad Request, 405 Method Not Allowed, etc.
                    throw new Error(`Error del servidor: ${response.status} ${response.statusText}`);
                }

                if (!contentType || !contentType.includes("application/json")) {
                    throw new Error("La respuesta del servidor no es JSON.");
                }
                
                return response.json();
            })
            .then(data => {
                console.log('✅ Datos procesados:', data);
                
                if (data.success) {
                    // 🟢 ACTUALIZAR LA UI DINÁMICAMENTE (Solo barra de progreso y estado)
                    actualizarBarra(pedidoId, data.data);
                    
                    // Mostrar alerta de éxito
                    mostrarAlerta('success', data.message);
                    
                    // Colapsar el panel de gestión automáticamente
                    const panel = document.getElementById(`panel-${pedidoId}`);
                    if (panel) {
                        if (typeof bootstrap !== 'undefined') {
                            const bsCollapse = bootstrap.Collapse.getInstance(panel) || new bootstrap.Collapse(panel, { toggle: false });
                            bsCollapse.hide();
                        } else {
                            panel.classList.remove('show');
                        }
                    }
                } else {
                    mostrarAlerta('danger', data.message || 'Error al actualizar el pedido.');
                }
            })
            .catch(error => {
                console.error('❌ Error en la petición:', error);
                mostrarAlerta('danger', `${error.message}`);
            })
            .finally(() => {
                // Restaurar botón
                restaurarBoton(btnSubmit, textoOriginal);
                console.log('🏁 Proceso finalizado');
            });
        });
    });
});

/**
 * Función auxiliar para restaurar el botón
 */
function restaurarBoton(btn, texto) {
    if(btn) {
        btn.disabled = false;
        btn.innerHTML = texto;
    }
}

/**
 * 🟢 FUNCIÓN PARA ACTUALIZAR LA BARRA DE PROGRESO Y COLORES
 */
function actualizarBarra(pedidoId, datos) {
    console.log(`🎨 Actualizando UI para pedido ${pedidoId}:`, datos);
    
    // NOTA: renderPath NO se usa en esta versión estable.
    const { progreso, colorEstado, nombreEstado } = datos;
    
    // Buscar el card del pedido
    const card = document.querySelector(`[data-pedido-id="${pedidoId}"]`);
    
    if (!card) {
        console.error(`❌ No se encontró el card para el pedido ${pedidoId}`);
        return;
    }
    
    console.log('✅ Card encontrado, actualizando elementos...');
    
    // 1. ACTUALIZAR EL BADGE DEL ESTADO
    const badge = card.querySelector('.badge.border'); 
    if (badge) {
        badge.className = `badge bg-${colorEstado} bg-opacity-25 text-${colorEstado} px-3 py-1 rounded-pill border border-${colorEstado}`;
        badge.textContent = nombreEstado.toUpperCase();
    }
    
    // 2. ACTUALIZAR EL PORCENTAJE DE TEXTO
    const porcentajeContainer = card.querySelector('.d-flex.justify-content-between.small.text-muted.mb-1');
    if (porcentajeContainer) {
        const porcentajeSpan = porcentajeContainer.querySelectorAll('span')[1]; 
        if (porcentajeSpan) {
            porcentajeSpan.textContent = `${progreso}%`;
        }
    }
    
    // 3. ACTUALIZAR LA BARRA DE PROGRESO
    const barraProgreso = card.querySelector('.progress-bar');
    if (barraProgreso) {
        barraProgreso.className = `progress-bar bg-${colorEstado} progress-bar-striped progress-bar-animated`;
        barraProgreso.style.width = `${progreso}%`;
        barraProgreso.setAttribute('aria-valuenow', progreso);
    }
    
    // 4. ACTUALIZAR EL BORDE DEL COMENTARIO
    const comentario = card.querySelector('p.border-start');
    if (comentario) {
        comentario.classList.remove('border-info', 'border-warning', 'border-primary', 'border-secondary', 'border-success', 'border-danger', 'border-dark');
        comentario.classList.add(`border-${colorEstado}`);
    }
    
    // 5. ANIMAR EL CAMBIO
    card.classList.add('actualizado');
    setTimeout(() => {
        card.classList.remove('actualizado');
    }, 1000);
    
    console.log('✅ UI actualizada completamente');
}

/**
 * 🟢 FUNCIÓN PARA MOSTRAR ALERTAS DINÁMICAS
 */
function mostrarAlerta(tipo, mensaje) {
    console.log(`📢 Mostrando alerta: [${tipo}] ${mensaje}`);
    
    const alerta = document.createElement('div');
    alerta.className = `alert alert-${tipo} alert-dismissible fade show shadow-sm border-0`;
    alerta.setAttribute('role', 'alert');
    alerta.innerHTML = `
        <i class="bi bi-${tipo === 'success' ? 'check-circle-fill' : 'exclamation-triangle-fill'} me-2"></i>${mensaje}
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    `;
    
    // Insertar alerta al principio del contenedor principal
    const contenedor = document.querySelector('.row.g-4');
    if (contenedor) {
        contenedor.parentNode.insertBefore(alerta, contenedor);
        
        // Auto-eliminar
        setTimeout(() => {
            alerta.classList.remove('show');
            setTimeout(() => alerta.remove(), 150);
        }, 5000);
    }
}

/**
 * 🟢 ESTILOS CSS INYECTADOS PARA ANIMACIONES
 */
if (!document.getElementById('pedidos-animations')) {
    const style = document.createElement('style');
    style.id = 'pedidos-animations';
    style.textContent = `
        .card.actualizado {
            animation: pulsoVerde 0.6s ease-in-out;
        }
        
        @keyframes pulsoVerde {
            0%, 100% { box-shadow: 0 0.125rem 0.25rem rgba(0,0,0,0.075); transform: scale(1); }
            50% { box-shadow: 0 0 15px rgba(25, 135, 84, 0.4); transform: scale(1.005); }
        }
        
        .progress-bar {
            transition: width 0.8s ease-in-out, background-color 0.4s ease;
        }
    `;
    document.head.appendChild(style);
}

// 🟢 PREVISUALIZADOR DE ARCHIVOS EN CONSOLA
document.addEventListener('change', function(e) {
    if (e.target && e.target.name === 'render') {
        const file = e.target.files[0];
        if (!file) return;

        console.log("📂 Archivo seleccionado:", file.name);
        console.log("📏 Tamaño:", (file.size / 1024 / 1024).toFixed(2), "MB");

        const is3D = file.name.toLowerCase().endsWith('.glb') || file.name.toLowerCase().endsWith('.gltf');
        const isImage = file.type.startsWith('image/');

        if (is3D) {
            console.log("🎲 Modelo 3D detectado.");
        } else if (isImage) {
            console.log("🖼️ Imagen detectada.");
        } else {
            console.warn("⚠️ Tipo de archivo no estándar para render.");
        }
    }
});