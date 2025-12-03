{{-- Modal de Detalles del Mensaje --}}
<div class="modal fade" id="modalDetalle" tabindex="-1" aria-labelledby="modalDetalleLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered modal-dialog-scrollable">
        <div class="modal-content modal-mensaje">
            <div class="modal-header">
                <div>
                    <h5 class="modal-title" id="modalDetalleLabel">
                        <i class="bi bi-envelope-open-fill me-2"></i>Detalles del Mensaje
                    </h5>
                    <small class="text-muted" id="detalleFecha"></small>
                </div>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                {{-- Información del remitente --}}
                <div class="detalle-section">
                    <h6 class="detalle-titulo">
                        <i class="bi bi-person-fill me-2"></i>Información del Remitente
                    </h6>
                    <div class="row g-3">
                        <div class="col-md-6">
                            <div class="detalle-campo">
                                <label>Nombre completo</label>
                                <div class="detalle-valor" id="detalleNombre"></div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="detalle-campo">
                                <label>Correo electrónico</label>
                                <div class="detalle-valor" id="detalleCorreo"></div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="detalle-campo">
                                <label>Teléfono</label>
                                <div class="detalle-valor" id="detalleTelefono"></div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="detalle-campo">
                                <label>Vía de contacto</label>
                                <div class="detalle-valor" id="detalleVia"></div>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- Mensaje --}}
                <div class="detalle-section">
                    <h6 class="detalle-titulo">
                        <i class="bi bi-chat-left-text-fill me-2"></i>Mensaje
                    </h6>
                    <div class="mensaje-completo" id="detalleMensaje"></div>
                </div>

                {{-- Estado y gestión --}}
                <div class="detalle-section">
                    <h6 class="detalle-titulo">
                        <i class="bi bi-gear-fill me-2"></i>Estado y Gestión
                    </h6>
                    <div class="row g-3">
                        <div class="col-md-4">
                            <div class="detalle-campo">
                                <label>Estado actual</label>
                                <div class="detalle-valor" id="detalleEstado"></div>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="detalle-campo">
                                <label>Usuario asociado</label>
                                <div class="detalle-valor" id="detalleUsuario"></div>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="detalle-campo">
                                <label>Atendido por</label>
                                <div class="detalle-valor" id="detalleAdmin"></div>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- Notas internas --}}
                <div class="detalle-section">
                    <h6 class="detalle-titulo">
                        <i class="bi bi-sticky-fill me-2"></i>Notas Internas
                    </h6>
                    <div class="notas-box" id="detalleNotas">
                        <em class="text-muted">Sin notas</em>
                    </div>
                </div>

                {{-- Información adicional --}}
                <div class="detalle-section">
                    <div class="info-box">
                        <div class="row g-3">
                            <div class="col-md-4">
                                <small class="text-muted d-block mb-1">ID del mensaje</small>
                                <strong id="detalleId"></strong>
                            </div>
                            <div class="col-md-4">
                                <small class="text-muted d-block mb-1">Fecha de envío</small>
                                <strong id="detalleFechaCompleta"></strong>
                            </div>
                            <div class="col-md-4">
                                <small class="text-muted d-block mb-1">Términos aceptados</small>
                                <strong id="detalleTerminos"></strong>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                    <i class="bi bi-x-circle me-2"></i>Cerrar
                </button>
                <button type="button" class="btn btn-primary" id="btnEditarDesdeDetalle">
                    <i class="bi bi-pencil-fill me-2"></i>Editar Mensaje
                </button>
            </div>
        </div>
    </div>
</div>

{{-- Modal de Edición --}}
<div class="modal fade" id="modalEditar" tabindex="-1" aria-labelledby="modalEditarLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered modal-dialog-scrollable">
        <div class="modal-content modal-mensaje">
            <div class="modal-header">
                <h5 class="modal-title" id="modalEditarLabel">
                    <i class="bi bi-pencil-square me-2"></i>Editar Mensaje
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form id="formEditarMensaje">
                <div class="modal-body">
                    <input type="hidden" id="editarMensajeId">

                    {{-- Información de solo lectura --}}
                    <div class="alert alert-info d-flex align-items-center gap-3 mb-4">
                        <i class="bi bi-info-circle-fill fs-4"></i>
                        <div>
                            <strong>Datos del remitente:</strong> 
                            <span class="text-muted">Los campos de nombre, correo, teléfono y mensaje son de solo lectura.</span>
                        </div>
                    </div>

                    <div class="info-box mb-4">
                        <div class="row g-3">
                            <div class="col-md-4">
                                <small class="text-muted d-block mb-1">Nombre</small>
                                <strong id="editarNombreReadonly"></strong>
                            </div>
                            <div class="col-md-4">
                                <small class="text-muted d-block mb-1">Correo</small>
                                <strong id="editarCorreoReadonly"></strong>
                            </div>
                            <div class="col-md-4">
                                <small class="text-muted d-block mb-1">Teléfono</small>
                                <strong id="editarTelefonoReadonly"></strong>
                            </div>
                        </div>
                    </div>

                    {{-- Campos editables --}}
                    <div class="row g-4">
                        <div class="col-md-6">
                            <label for="editarEstado" class="form-label required">Estado</label>
                            <select class="form-select" id="editarEstado" name="estado" required>
                                <option value="pendiente">Pendiente</option>
                                <option value="atendido">Atendido</option>
                                <option value="archivado">Archivado</option>
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label for="editarVia" class="form-label required">Vía de contacto</label>
                            <select class="form-select" id="editarVia" name="via" required>
                                <option value="formulario">Formulario</option>
                                <option value="whatsapp">WhatsApp</option>
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label for="editarUsuarioId" class="form-label">Usuario Asociado (ID)</label>
                            <input type="number" class="form-control" id="editarUsuarioId" name="usuarioId" placeholder="Ej: 7">
                            <small class="form-text text-muted">ID del usuario cliente relacionado</small>
                        </div>
                        <div class="col-md-6">
                            <label for="editarUsuarioIdAdmin" class="form-label">Admin que Atiende (ID)</label>
                            <input type="number" class="form-control" id="editarUsuarioIdAdmin" name="usuarioIdAdmin" placeholder="Ej: 2">
                            <small class="form-text text-muted">ID del administrador que atiende</small>
                        </div>
                        <div class="col-12">
                            <label for="editarNotas" class="form-label">Notas Internas</label>
                            <textarea class="form-control" id="editarNotas" name="notas" rows="4" maxlength="1000" placeholder="Agrega notas sobre este mensaje..."></textarea>
                            <small class="form-text text-muted">Máximo 1000 caracteres</small>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                        <i class="bi bi-x-circle me-2"></i>Cancelar
                    </button>
                    <button type="submit" class="btn btn-create">
                        <i class="bi bi-check-circle me-2"></i>Guardar Cambios
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>