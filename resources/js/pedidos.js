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
            
            // ⚠️ CRÍTICO: Laravel/PHP tienen problemas leyendo archivos en peticiones PUT directas.
            // Solución: Enviamos como POST y agregamos '_method' = 'PUT' para que Laravel entienda la intención.
            formData.append('_method', 'PUT'); 

            const action = form.getAttribute('action');
            
            // Obtener botón de submit para mostrar loading
            const btnSubmit = form.querySelector('button[type="submit"]');
            const textoOriginal = btnSubmit ? btnSubmit.innerHTML : 'Guardar';
            
            // Deshabilitar botón y mostrar spinner
            if(btnSubmit) {
                btnSubmit.disabled = true;
                btnSubmit.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span>Guardando...';
            }
            
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
                method: 'POST', // IMPORTANTE: Usamos POST para que viajen los archivos correctamente
                headers: {
                    'X-CSRF-TOKEN': csrfToken,
                    'Accept': 'application/json', // Forzamos a Laravel a responder JSON si hay error
                    // NO AGREGAR 'Content-Type': 'multipart/form-data', el navegador lo hace automático con el boundary correcto.
                },
                body: formData 
            })
            .then(async response => {
                const contentType = response.headers.get("content-type");
                
                // Manejo específico de error de validación (422)
                if (response.status === 422) {
                    const errorData = await response.json();
                    let mensajeError = errorData.message || "Error de validación.";
                    
                    // Si hay errores específicos de campos, mostrarlos
                    if (errorData.errors) {
                        const erroresDetallados = Object.values(errorData.errors).flat().join(' ');
                        mensajeError += ` ${erroresDetallados}`;
                    }
                    throw new Error(mensajeError);
                }

                if (!response.ok) {
                    // Captura 400 Bad Request, 500 Server Error, etc.
                    let mensaje = `Error del servidor (${response.status})`;
                    try {
                        const errorJson = await response.json();
                        if (errorJson.message) mensaje = errorJson.message;
                    } catch (e) {
                        // Si no es JSON, mantenemos el mensaje genérico
                    }
                    throw new Error(mensaje);
                }

                if (!contentType || !contentType.includes("application/json")) {
                    throw new Error("La respuesta del servidor no es válida (no es JSON).");
                }
                
                return response.json();
            })
            .then(data => {
                console.log('✅ Datos procesados:', data);
                
                if (data.success) {
                    // 🟢 ACTUALIZAR LA UI DINÁMICAMENTE
                    if(data.data) {
                        actualizarBarra(pedidoId, data.data);
                    }
                    
                    // Mostrar alerta de éxito
                    mostrarAlerta('success', data.message || 'Pedido actualizado correctamente.');
                    
                    // Colapsar el panel de gestión automáticamente
                    const panel = document.getElementById(`panel-${pedidoId}`);
                    if (panel) {
                        // Intentar usar la API de Bootstrap si está disponible
                        if (typeof bootstrap !== 'undefined' && bootstrap.Collapse) {
                            const bsCollapse = bootstrap.Collapse.getInstance(panel) || new bootstrap.Collapse(panel, { toggle: false });
                            bsCollapse.hide();
                        } else {
                            // Fallback manual
                            panel.classList.remove('show');
                        }
                    }
                } else {
                    mostrarAlerta('danger', data.message || 'No se pudo actualizar el pedido.');
                }
            })
            .catch(error => {
                console.error('❌ Error en la petición:', error);
                mostrarAlerta('danger', error.message);
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
    
    const { progreso, colorEstado, nombreEstado } = datos;
    
    // Buscar el card del pedido
    const card = document.querySelector(`[data-pedido-id="${pedidoId}"]`);
    
    if (!card) {
        console.warn(`⚠️ No se encontró el card para el pedido ${pedidoId} en el DOM.`);
        return;
    }
    
    // 1. ACTUALIZAR EL BADGE DEL ESTADO
    const badge = card.querySelector('.badge.border'); 
    if (badge) {
        // Mantenemos las clases base y actualizamos las de color
        badge.className = `badge bg-${colorEstado} bg-opacity-25 text-${colorEstado} px-3 py-1 rounded-pill border border-${colorEstado}`;
        badge.textContent = nombreEstado ? nombreEstado.toUpperCase() : 'DESCONOCIDO';
    }
    
    // 2. ACTUALIZAR EL PORCENTAJE DE TEXTO
    const porcentajeContainer = card.querySelector('.d-flex.justify-content-between.small.text-muted.mb-1');
    if (porcentajeContainer) {
        // Asumimos que el porcentaje es el segundo span
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
        // Removemos clases antiguas de borde con regex simple o lista
        const clasesBorde = ['border-info', 'border-warning', 'border-primary', 'border-secondary', 'border-success', 'border-danger', 'border-dark'];
        comentario.classList.remove(...clasesBorde);
        comentario.classList.add(`border-${colorEstado}`);
    }
    
    // 5. ANIMAR EL CAMBIO
    card.classList.add('actualizado');
    setTimeout(() => {
        card.classList.remove('actualizado');
    }, 1000);
}

/**
 * 🟢 FUNCIÓN PARA MOSTRAR ALERTAS DINÁMICAS
 */
function mostrarAlerta(tipo, mensaje) {
    // Evitar acumulación de alertas
    const alertasPrevias = document.querySelectorAll('.alert-dinamica');
    alertasPrevias.forEach(a => a.remove());

    const alerta = document.createElement('div');
    alerta.className = `alert alert-${tipo} alert-dismissible fade show shadow-sm border-0 alert-dinamica`;
    alerta.style.zIndex = '1050'; // Asegurar que se vea sobre otros elementos
    alerta.setAttribute('role', 'alert');
    alerta.innerHTML = `
        <i class="bi bi-${tipo === 'success' ? 'check-circle-fill' : 'exclamation-triangle-fill'} me-2"></i>${mensaje}
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    `;
    
    // Insertar alerta antes de la fila principal
    const contenedor = document.querySelector('.row.g-4');
    if (contenedor && contenedor.parentNode) {
        contenedor.parentNode.insertBefore(alerta, contenedor);
    } else {
        // Fallback al inicio del container principal
        const mainContainer = document.querySelector('.container') || document.body;
        mainContainer.prepend(alerta);
    }
    
    // Auto-eliminar a los 5 segundos
    setTimeout(() => {
        if(alerta) {
            alerta.classList.remove('show');
            setTimeout(() => alerta.remove(), 150);
        }
    }, 5000);
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

// 🟢 PREVISUALIZADOR DE ARCHIVOS EN CONSOLA (Opcional)
document.addEventListener('change', function(e) {
    if (e.target && e.target.name === 'render') {
        const file = e.target.files[0];
        if (!file) return;

        console.log("📂 Archivo seleccionado:", file.name);
        // Validar tamaño en el cliente antes de enviar (ej. 10MB)
        const tamanoMB = file.size / 1024 / 1024;
        console.log("📏 Tamaño:", tamanoMB.toFixed(2), "MB");

        if (tamanoMB > 10) {
            mostrarAlerta('warning', `El archivo pesa ${tamanoMB.toFixed(2)}MB. El límite recomendado es 10MB.`);
        }
    }
});