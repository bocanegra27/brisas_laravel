#!/bin/bash

# Script de Respaldo Backend Seguro - Java/Spring Boot
# Estándares Profesionales 2024-2026

set -euo pipefail

# Cargar configuración
SCRIPT_DIR="$(cd "$(dirname "${BASH_SOURCE[0]}")" && pwd)"
source "${SCRIPT_DIR}/.env.backup"

# Configuración
DATE=$(date +%Y%m%d_%H%M%S)
BACKUP_DIR="${BACKUP_DIR:-/backups/brisas/backend}"
PROJECT_DIR="${PROJECT_DIR:-/c/Users/bocan/IdeaProjects/API-brisas-Gems}"
TEMP_DIR="/tmp/brisas_backend_backup_${DATE}"
LOG_FILE="$BACKUP_DIR/logs/backend_backup_secure_$DATE.log"

mkdir -p "$BACKUP_DIR/logs" "$TEMP_DIR"

# Logging
log() { echo "[$(date '+%Y-%m-%d %H:%M:%S')] $1" | tee -a "$LOG_FILE"; }
log_success() { echo -e "\033[0;32m[SUCCESS]\033[0m $1" | tee -a "$LOG_FILE"; }
log_error() { echo -e "\033[0;31m[ERROR]\033[0m $1" | tee -a "$LOG_FILE"; }

# Notificación
send_notification() {
    if [[ "${WEBHOOK_ENABLED:-false}" == "true" && -n "${WEBHOOK_URL:-}" ]]; then
        curl -X POST -H 'Content-type: application/json' \
            --data "{\"text\":\"Backend Backup $1: $2\"}" \
            "$WEBHOOK_URL" 2>/dev/null || true
    fi
}

# Cifrado GPG
encrypt_file() {
    local input="$1"
    if [[ "${BACKUP_ENCRYPTION_ENABLED:-true}" == "true" ]]; then
        gpg --batch --yes --trust-model always \
            --encrypt --recipient "${GPG_RECIPIENT:-backup@brisas.com}" \
            --output "${input}.gpg" "$input" && rm -f "$input"
    fi
}

# Respaldo de código fuente
backup_source() {
    log "Respaldando código fuente Java..."
    local backup_file="$TEMP_DIR/backend_source_${DATE}.tar.gz"
    
    tar --exclude='target/' --exclude='.idea/' --exclude='*.log' \
        -czf "$backup_file" -C "$PROJECT_DIR" .
    
    encrypt_file "$backup_file"
}

# Respaldo de aplicación compilada
backup_jar() {
    log "Respaldando aplicación .jar..."
    local target_dir="$PROJECT_DIR/target"

# Sincronización Rclone
sync_to_cloud() {
    if [[ -n "${RCLONE_REMOTE:-}" ]]; then
        rclone sync "$TEMP_DIR" "${RCLONE_REMOTE}:${RCLONE_REMOTE_PATH:-backups/brisas}/backend/$DATE"
    fi
}

# Principal
main() {
    log "INICIANDO RESPALDO BACKEND SEGURO"
    
    backup_source || { send_notification "ERROR" "Fallo respaldo código fuente"; exit 1; }
    backup_jar || send_notification "WARNING" "No se encontró .jar"
    sync_to_cloud || send_notification "WARNING" "Fallo sincronización nube"
    
    mkdir -p "$BACKUP_DIR/$DATE"
    mv "$TEMP_DIR"/* "$BACKUP_DIR/$DATE/" 2>/dev/null || true
    rm -rf "$TEMP_DIR"
    
    log_success "Respaldo backend completado"
    send_notification "SUCCESS" "Respaldo backend completado"
}

main "$@"
