<style>
.timeline {
    position: relative;
    padding-left: 30px;
}

.timeline::before {
    content: '';
    position: absolute;
    left: 15px;
    top: 0;
    bottom: 0;
    width: 2px;
    background: #e9ecef;
}

.timeline-item {
    position: relative;
    margin-bottom: 20px;
}

.timeline-marker {
    position: absolute;
    left: -23px;
    top: 5px;
    width: 16px;
    height: 16px;
    border-radius: 50%;
    background: white;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 10px;
}

.timeline-content {
    background: #f8f9fa;
    border: 1px solid #e9ecef;
    border-radius: 8px;
    padding: 15px;
    margin-left: 10px;
}

.timeline-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 8px;
}

.timeline-header strong {
    color: #495057;
}

.estado-badge {
    padding: 4px 12px;
    border-radius: 20px;
    font-size: 0.875rem;
    font-weight: 500;
    display: inline-block;
}

.estado-pendiente { background-color: #fef3c7; color: #92400e; }
.estado-confirmado { background-color: #d1fae5; color: #065f46; }
.estado-producción { background-color: #dbeafe; color: #1e40af; }
.estado-entregado { background-color: #d1fae5; color: #065f46; }
.estado-desconocido { background-color: #e5e7eb; color: #6b7280; }

/* Estilos para sección de renders */
.render-section {
    background: #f8f9fa;
    border: 1px solid #e9ecef;
    border-radius: 12px;
    padding: 20px;
}

.render-section h6 {
    color: #495057;
    font-weight: 600;
    margin-bottom: 15px;
}

.render-container {
    text-align: center;
}

.render-container img {
    transition: transform 0.2s ease-in-out;
}

.render-container img:hover {
    transform: scale(1.05);
}

/* Estilos para galería de productos */
.galeria-section {
    background: #f8f9fa;
    border: 1px solid #e9ecef;
    border-radius: 12px;
    padding: 20px;
}

.galeria-section h6 {
    color: #495057;
    font-weight: 600;
    margin-bottom: 15px;
}

.galeria-item {
    position: relative;
    overflow: hidden;
    border-radius: 8px;
    transition: transform 0.2s ease-in-out;
}

.galeria-item:hover {
    transform: scale(1.05);
}

.galeria-overlay {
    position: absolute;
    top: 0;
    left: 0;
    right: 0;
    bottom: 0;
    background: rgba(0, 0, 0, 0.5);
    display: flex;
    align-items: center;
    justify-content: center;
    opacity: 0;
    transition: opacity 0.2s ease-in-out;
    color: white;
    font-size: 20px;
}

.galeria-item:hover .galeria-overlay {
    opacity: 1;
}

/* Estilos responsivos */
@media (max-width: 768px) {
    .timeline {
        padding-left: 20px;
    }
    
    .timeline-marker {
        left: -18px;
        width: 14px;
        height: 14px;
        font-size: 8px;
    }
    
    .timeline-content {
        padding: 12px;
        margin-left: 8px;
    }
    
    .render-section,
    .galeria-section {
        padding: 15px;
    }
}
</style>
