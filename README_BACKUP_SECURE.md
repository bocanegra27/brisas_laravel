# Sistema de Respaldo Seguro - Brisas Laravel

## Estándares Profesionales 2024-2026

Este sistema de respaldo implementa las mejores prácticas de seguridad y redundancia:

### Características de Seguridad

- **Cifrado GPG**: Todos los respaldos se cifran con clave pública
- **MySQL Seguro**: Credenciales protegidas con archivo temporal `.my.cnf`
- **Regla 3-2-1**: 3 copias, 2 medios diferentes, 1 fuera del sitio
- **Notificaciones**: Alertas automáticas vía Slack/Discord
- **Logs Rotativos**: Previenen saturación del disco

## Instalación

### 1. Instalar Dependencias

```bash
# Debian/Ubuntu
sudo apt-get update
sudo apt-get install gnupg rclone mysql-client

# macOS
brew install gnupg rclone mysql-client

# Windows (con Chocolatey)
choco install gpg rclone mysql
```

### 2. Configurar Variables

```bash
# Copiar archivo de configuración
cp .env.backup.example .env.backup

# Editar configuración
nano .env.backup
```

### 3. Configurar GPG

```bash
# Generar clave para respaldos (opcional)
gpg --full-generate-key

# O importar clave existente
gpg --import backup_public_key.asc
```

### 4. Configurar Rclone

```bash
# Configurar destino en la nube
rclone config

# Ejemplo para Google Drive
rclone config create brisas_drive drive
```

## Uso

### Scripts Bash (Recomendado para producción)

```bash
# Respaldo diario (solo datos dinámicos)
./backup_secure.sh

# Respaldo completo (incluye código fuente)
./backup_secure.sh --full

# Respaldo backend
./backup_backend_secure.sh
```

### Comando Artisan (Opción alternativa)

```bash
# Respaldo seguro
php artisan backup:secure

# Respaldo completo
php artisan backup:secure --full

# Simulación (prueba)
php artisan backup:secure --dry-run
```

## Archivos Generados

```
/backups/brisas/
├── 20240224_143022/
│   ├── database_20240224_143022.sql.gz.gpg
│   ├── storage_20240224_143022.tar.gz.gpg
│   └── backup_report_20240224_143022.txt
├── logs/
│   ├── backup_secure_20240224.log
│   └── backup_report_20240224_143022.txt
└── backend/
    └── 20240224_143022/
        ├── backend_source_20240224_143022.tar.gz.gpg
        └── backend_app_20240224_143022.jar.gpg
```

##  Descifrado de Respaldos

```bash
# Descifrar archivo específico
gpg --output database.sql.gz --decrypt database.sql.gz.gpg

# Descifrar todos los archivos en un directorio
gpg --decrypt-files *.gpg

# Descomprimir base de datos
gunzip database.sql.gz

# Restaurar base de datos
mysql -u root -p sistema2 < database.sql
```

## Configuración de Nube

### Google Drive con Rclone

```bash
# Configurar
rclone config create brisas_drive drive

# Verificar conexión
rclone lsd brisas_drive:

# Sincronizar manualmente
rclone sync /backups/brisas brisas_drive:backups/brisas
```

### AWS S3 con Rclone

```bash
# Configurar
rclone config create brisas_s3 s3

# Usar bucket específico
rclone sync /backups/brisas brisas_s3:brisas-backups
```

## Configuración de Notificaciones

### Slack Webhook

1. Crear app en Slack: https://api.slack.com/apps
2. Activar "Incoming Webhooks"
3. Copiar URL y agregar a `.env.backup`:
   ```
   WEBHOOK_URL=https://hooks.slack.com/services/YOUR/SLACK/WEBHOOK
   WEBHOOK_ENABLED=true
   ```

### Discord Webhook

1. Configuración del servidor → Integraciones → Webhooks
2. Crear webhook y copiar URL
3. Agregar a `.env.backup`

## Automatización

### Cron Job (Linux/macOS)

```bash
# Editar crontab
crontab -e

# Agregar respaldo diario a las 2 AM
0 2 * * * cd /ruta/a/brisas_laravel && ./backup_secure.sh

# Respaldo completo semanal (domingos a 3 AM)
0 3 * * 0 cd /ruta/a/brisas_laravel && ./backup_secure.sh --full
```

### Laravel Scheduler

```php
// app/Console/Kernel.php
protected function schedule(Schedule $schedule)
{
    $schedule->command('backup:secure')
             ->dailyAt('02:00')
             ->withoutOverlapping();
             
    $schedule->command('backup:secure --full')
             ->sundays()
             ->at('03:00')
             ->withoutOverlapping();
}
```

### Windows Task Scheduler

```powershell
# Crear tarea programada
$action = New-ScheduledTaskAction -Execute "C:\brisas_laravel\backup_secure.sh" -WorkingDirectory "C:\brisas_laravel"
$trigger = New-ScheduledTaskTrigger -Daily -At 2am
Register-ScheduledTask -Action $action -Trigger $trigger -TaskName "BrisasBackup" -Description "Respaldo seguro Brisas"
```

## Monitoreo y Mantenimiento

### Verificar Estado

```bash
# Ver últimos logs
tail -f /backups/brisas/logs/backup_secure_$(date +%Y%m%d).log

# Ver espacio utilizado
du -sh /backups/brisas

# Verificar archivos en la nube
rclone ls brisas_drive:backups/brisas
```

### Pruebas de Restauración

```bash
# Simular restauración completa
./test_restoration.sh 20240224_143022

# Verificar integridad de GPG
gpg --list-packets *.gpg
```

## Solución de Problemas

### Error: "GPG recipient not found"

```bash
# Verificar claves disponibles
gpg --list-keys

# Importar clave pública
gpg --import backup_public_key.asc
```

### Error: "Rclone remote not configured"

```bash
# Verificar configuración
rclone config show

# Reconfigurar
rclone config
```

### Error: "MySQL access denied"

```bash
# Verificar archivo .env.backup
cat .env.backup | grep DB_BACKUP_

# Probar conexión manual
mysql -h127.0.0.1 -uroot -p sistema2
```

## Métricas y Reportes

### Estadísticas de Respaldo

```bash
# Generar reporte mensual
./generate_monthly_report.sh

# Ver tamaño de respaldos por día
du -sh /backups/brisas/* | sort -hr
```

### Alertas Personalizadas

```bash
# Script de monitoreo
#!/bin/bash
BACKUP_DIR="/backups/brisas"
TODAY=$(date +%Y%m%d)

if [ ! -d "$BACKUP_DIR/$TODAY"* ]; then
    curl -X POST -H 'Content-type: application/json' \
        --data '{"text":"🚨 ALERTA: No se encontró respaldo de hoy"}' \
        "$WEBHOOK_URL"
fi
```

## Mejores Prácticas

1. **Pruebas mensuales**: Restaurar en entorno de prueba
2. **Rotación de claves**: Cambiar clave GPG cada 6 meses
3. **Monitoreo**: Configurar alertas por email/webhook
4. **Documentación**: Mantener actualizado este README
5. **Cumplimiento**: Verificar regulaciones de datos (GDPR, etc.)

---

## Soporte

- **Documentación oficial**: [Laravel Backup](https://laravel.com/docs/backup)
- **GPG Manual**: [GnuPG](https://www.gnupg.org/documentation/)
- **Rclone Docs**: [Rclone](https://rclone.org/docs/)
- **Issues**: Crear ticket en repositorio del proyecto

---

**Versión**: 2.0 (Segura)  
**Fecha**: 24 de Febrero de 2026  
**Estándar**: Enterprise Security & Compliance
