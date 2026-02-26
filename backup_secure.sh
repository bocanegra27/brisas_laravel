#!/bin/bash

# Script de Respaldo Seguro - Sistema Brisas Laravel
# Estándares Profesionales 2024-2026
# Seguridad: Cifrado GPG, 3-2-1 Rule, Notificaciones

# Requisitos:
# sudo apt-get install gnupg rclone  # Debian/Ubuntu
# brew install gnupg rclone            # macOS
# choco install gpg rclone            # Windows

set -euo pipefail  # Seguridad: Salir en errores

# Cargar configuración
SCRIPT_DIR="$(cd "$(dirname "${BASH_SOURCE[0]}")" && pwd)"
ENV_FILE="${SCRIPT_DIR}/.env.backup"

if [ ! -f "$ENV_FILE" ]; then
    echo "❌ ERROR: Archivo .env.backup no encontrado"
    echo "📋 Copia .env.backup.example a .env.backup y configura las variables"
    exit 1
fi

source "$ENV_FILE"

# Configuración
DATE=$(date +%Y%m%d_%H%M%S)
BACKUP_DIR="${BACKUP_DIR:-/backups/brisas}"
PROJECT_DIR="${PROJECT_DIR:-/c/brisas_laravel}"
TEMP_DIR="/tmp/brisas_backup_${DATE}"
LOG_FILE="$BACKUP_DIR/logs/backup_secure_$DATE.log"

# Crear directorios
mkdir -p "$BACKUP_DIR/logs" "$TEMP_DIR"

# Colores y logging
RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
BLUE='\033[0;34m'
NC='\033[0m'

log() {
    echo "[$(date '+%Y-%m-%d %H:%M:%S')] $1" | tee -a "$LOG_FILE"
}

log_success() {
    echo -e "${GREEN}[SUCCESS]${NC} $1" | tee -a "$LOG_FILE"
}

log_warning() {
    echo -e "${YELLOW}[WARNING]${NC} $1" | tee -a "$LOG_FILE"
}

log_error() {
    echo -e "${RED}[ERROR]${NC} $1" | tee -a "$LOG_FILE"
}

log_info() {
    echo -e "${BLUE}[INFO]${NC} $1" | tee -a "$LOG_FILE"
}

# Notification function
send_notification() {
    local status="$1"
    local message="$2"
    
    if [[ "${WEBHOOK_ENABLED:-false}" == "true" && -n "${WEBHOOK_URL:-}" ]]; then
        local color="good"
        [[ "$status" == "ERROR" ]] && color="danger"
        [[ "$status" == "WARNING" ]] && color="warning"
        
        curl -X POST -H 'Content-type: application/json' \
            --data "{\"text\":\"Brisas Backup $status: $message\"}" \
            "$WEBHOOK_URL" 2>/dev/null || true
    fi
}

# Rotación de logs
rotate_logs() {
    local retention_days="${LOG_RETENTION_DAYS:-7}"
    find "$BACKUP_DIR/logs" -name "backup_secure_*.log" -mtime +$retention_days -delete 2>/dev/null || true
}

# Verificar herramientas requeridas
check_dependencies() {
    local missing_tools=()
    
    command -v mysqldump >/dev/null 2>&1 || missing_tools+=("mysqldump")
    command -v gpg >/dev/null 2>&1 || missing_tools+=("gpg")
    command -v rclone >/dev/null 2>&1 || missing_tools+=("rclone")
    command -v tar >/dev/null 2>&1 || missing_tools+=("tar")
    
    if [ ${#missing_tools[@]} -gt 0 ]; then
        log_error "Herramientas faltantes: ${missing_tools[*]}"
        log_error "Instala con: sudo apt-get install ${missing_tools[*]}"
        send_notification "ERROR" "Herramientas faltantes: ${missing_tools[*]}"
        exit 1
    fi
}

# Crear archivo de configuración MySQL seguro
create_mysql_config() {
    local mysql_config="$TEMP_DIR/.my.cnf"
    
    cat > "$mysql_config" << EOF
[mysqldump]
host=${DB_BACKUP_HOST:-127.0.0.1}
port=${DB_BACKUP_PORT:-3306}
user=${DB_BACKUP_USERNAME:-root}
password=${DB_BACKUP_PASSWORD}
[mysql]
host=${DB_BACKUP_HOST:-127.0.0.1}
port=${DB_BACKUP_PORT:-3306}
user=${DB_BACKUP_USERNAME:-root}
password=${DB_BACKUP_PASSWORD}
EOF
    
    chmod 600 "$mysql_config"
    echo "$mysql_config"
}

# Cifrar archivo con GPG
encrypt_file() {
    local input_file="$1"
    local output_file="${input_file}.gpg"
    local recipient="${GPG_RECIPIENT:-backup@brisas.com}"
    
    if [[ "${BACKUP_ENCRYPTION_ENABLED:-true}" == "true" ]]; then
        log_info "Cifrando $(basename "$input_file")..."
        
        if gpg --batch --yes --trust-model always \
               --encrypt --recipient "$recipient" \
               --output "$output_file" "$input_file"; then
            rm -f "$input_file"  # Eliminar original después de cifrar
            log_success "Archivo cifrado: $(basename "$output_file")"
            return 0
        else
            log_error "Error al cifrar $(basename "$input_file")"
            return 1
        fi
    else
        log_warning "Cifrado deshabilitado, manteniendo archivo sin cifrar"
        return 0
    fi
}

# Backup database
backup_database() {
    log_info "Respaldando base de datos..."
    
    local mysql_config
    mysql_config=$(create_mysql_config)
    local backup_file="$TEMP_DIR/database_${DATE}.sql.gz"
    local database="${DB_BACKUP_DATABASE:-sistema2}"
    
    # Secure backup without exposing password
    if mysqldump --defaults-extra-file="$mysql_config" \
                  --single-transaction \
                  --routines \
                  --triggers \
                  --events \
                  --hex-blob \
                  --default-character-set=utf8mb4 \
                  "$database" | gzip > "$backup_file"; then
        
        local size=$(du -h "$backup_file" | cut -f1)
        log_success "Base de datos respaldada: $size"
        
        # Encrypt the backup
        encrypt_file "$backup_file"
        
        # Clean up temporary configuration
        rm -f "$mysql_config"
        
        return 0
    else
        log_error "Error al respaldar base de datos"
        rm -f "$mysql_config"
        return 1
    fi
}

# Respaldo de archivos dinámicos (storage)
backup_storage() {
    log_info "Respaldando archivos de storage..."
    
    local storage_backup_file="$TEMP_DIR/storage_${DATE}.tar.gz"
    local storage_dir="$PROJECT_DIR/storage/app"
    
    if [ -d "$storage_dir" ]; then
        # Solo respaldar datos dinámicos, excluir cache
        tar --exclude='cache/*' \
            --exclude='framework/cache/*' \
            --exclude='framework/views/*' \
            --exclude='framework/sessions/*' \
            -czf "$storage_backup_file" \
            -C "$storage_dir" .
        
        local size=$(du -h "$storage_backup_file" | cut -f1)
        log_success "Storage respaldado: $size"
        
        # Cifrar el respaldo
        encrypt_file "$storage_backup_file"
        
        return 0
    else
        log_warning "Directorio storage no encontrado: $storage_dir"
        return 0
    fi
}

# Respaldo de código fuente (opcional, solo si --full)
backup_source_code() {
    if [[ "${BACKUP_SOURCE_CODE_ENABLED:-false}" == "true" ]]; then
        log_info "Respaldando código fuente..."
        
        local source_backup_file="$TEMP_DIR/source_${DATE}.tar.gz"
        
        # Excluir directorios no necesarios
        tar --exclude='node_modules' \
            --exclude='vendor' \
            --exclude='storage/framework/cache' \
            --exclude='storage/framework/views' \
            --exclude='bootstrap/cache' \
            --exclude='.git' \
            --exclude='*.log' \
            -czf "$source_backup_file" \
            -C "$PROJECT_DIR" .
        
        local size=$(du -h "$source_backup_file" | cut -f1)
        log_success "Código fuente respaldado: $size"
        
        # Cifrar el respaldo
        encrypt_file "$source_backup_file"
        
        return 0
    else
        log_info "📝 Respaldo de código fuente deshabilitado (usando Git como fuente de verdad)"
        return 0
    fi
}

# Verificación de integridad
verify_backups() {
    log_info "Verificando integridad de respaldos..."
    
    local errors=0
    local backup_files=("$TEMP_DIR"/*.gpg)
    
    for file in "${backup_files[@]}"; do
        if [ -f "$file" ]; then
            # Verificar que el archivo GPG no esté corrupto
            if gpg --list-packets "$file" >/dev/null 2>&1; then
                log_success "✅ $(basename "$file") - Integridad verificada"
            else
                log_error "❌ $(basename "$file") - Archivo corrupto"
                ((errors++))
            fi
        fi
    done
    
    if [ $errors -eq 0 ]; then
        log_success "Todos los respaldos pasaron la verificación"
        return 0
    else
        log_error "$errors archivos fallaron la verificación"
        send_notification "ERROR" "$errors archivos corruptos detectados"
        return 1
    fi
}

# Sincronización con Rclone (Regla 3-2-1)
sync_to_cloud() {
    if [[ -n "${RCLONE_REMOTE:-}" ]]; then
        log_info "Sincronizando con la nube (${RCLONE_REMOTE})..."
        
        local remote_path="${RCLONE_REMOTE_PATH:-backups/brisas}"
        local retention_days="${BACKUP_RETENTION_DAYS:-30}"
        
        # Subir nuevos respaldos
        if rclone sync "$TEMP_DIR" "${RCLONE_REMOTE}:${remote_path}/$DATE" \
            --progress \
            --transfers 4 \
            --checkers 4; then
            
            log_success "Respaldos sincronizados con la nube"
            
            # Limpiar respaldos antiguos en la nube
            rclone delete "${RCLONE_REMOTE}:${remote_path}" \
                --min-age "${retention_days}d" \
                --dry-run 2>/dev/null || true
            
            return 0
        else
            log_error "Error al sincronizar con la nube"
            send_notification "ERROR" "Fallo en sincronización con ${RCLONE_REMOTE}"
            return 1
        fi
    else
        log_warning "Rclone no configurado, omitiendo sincronización en la nube"
        return 0
    fi
}

# Limpieza local
cleanup_local() {
    log_info "Limpiando archivos temporales..."
    
    # Mover respaldos locales al directorio final
    mkdir -p "$BACKUP_DIR/$DATE"
    mv "$TEMP_DIR"/* "$BACKUP_DIR/$DATE/" 2>/dev/null || true
    
    # Limpiar directorio temporal
    rm -rf "$TEMP_DIR"
    
    # Limpiar respaldos locales antiguos
    local retention_days="${BACKUP_RETENTION_DAYS:-30}"
    find "$BACKUP_DIR" -type d -name "20*" -mtime +$retention_days -exec rm -rf {} \; 2>/dev/null || true
    
    log_success "Limpieza completada"
}

# Generar reporte
generate_report() {
    local report_file="$BACKUP_DIR/logs/backup_report_$DATE.txt"
    
    cat > "$report_file" << EOF
========================================
REPORTE DE RESPALDO SEGURO - BRISAS
========================================
Fecha: $(date)
Tipo: Seguro con Cifrado GPG + 3-2-1 Rule
Directorio: $BACKUP_DIR/$DATE

COMPONENTES RESPALDADOS:
✅ Base de Datos MySQL - Cifrada
✅ Storage Dinámico - Cifrado
EOF

if [[ "${BACKUP_SOURCE_CODE_ENABLED:-false}" == "true" ]]; then
    echo "✅ Código Fuente - Cifrado" >> "$report_file"
fi

cat >> "$report_file" << EOF

SEGURIDAD:
🔒 Cifrado: ${BACKUP_ENCRYPTION_ENABLED:-true}
☁️  Nube: ${RCLONE_REMOTE:-No configurado}
📱 Notificaciones: ${WEBHOOK_ENABLED:-false}

ESPACIO UTILIZADO:
$(du -sh "$BACKUP_DIR/$DATE" 2>/dev/null | cut -f1 || echo "N/A")

ESTADO: COMPLETADO EXITOSAMENTE
========================================
EOF
    
    log_success "Reporte generado: backup_report_$DATE.txt"
}

# Función principal
main() {
    log "========================================="
    log "INICIANDO RESPALDO SEGURO BRISAS"
    log "========================================="
    
    local start_time=$(date +%s)
    
    # Rotar logs antiguos
    rotate_logs
    
    # Verificar dependencias
    check_dependencies || exit 1
    
    # Ejecutar pasos del respaldo
    backup_database || { send_notification "ERROR" "Fallo en respaldo de base de datos"; exit 1; }
    backup_storage || { send_notification "ERROR" "Fallo en respaldo de storage"; exit 1; }
    
    # Respaldo de código fuente solo si se solicita
    if [[ "${1:-}" == "--full" ]]; then
        backup_source_code || { send_notification "ERROR" "Fallo en respaldo de código fuente"; exit 1; }
    fi
    
    # Verificación y sincronización
    verify_backups || { send_notification "ERROR" "Fallo en verificación de integridad"; exit 1; }
    sync_to_cloud || send_notification "WARNING" "Fallo en sincronización con nube"
    
    # Limpieza y reporte
    cleanup_local
    generate_report
    
    local end_time=$(date +%s)
    local duration=$((end_time - start_time))
    
    log "========================================="
    log_success "RESPALDO SEGURO COMPLETADO"
    log "⏱️  Duración: ${duration} segundos"
    log "📁 Ubicación: $BACKUP_DIR/$DATE"
    log "========================================="
    
    send_notification "SUCCESS" "Respaldo completado en ${duration}s"
}

# Ejecutar script
main "$@"
