<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Process;

class BackupSystemSecure extends Command
{
    protected $signature = 'backup:secure 
                            {--full : Incluir código fuente (solo si es necesario)}
                            {--path= : Directorio de destino personalizado}
                            {--dry-run : Simular ejecución sin realizar respaldo}';
    
    protected $description = 'Sistema de respaldo seguro con cifrado GPG y 3-2-1 rule';

    protected $backupPath;
    protected $date;
    protected $config;

    public function handle()
    {
        $this->info('Iniciando sistema de respaldo seguro Brisas Laravel...');
        $this->line('');

        // Cargar configuración
        $this->loadConfiguration();
        
        // Configuración inicial
        $this->date = now()->format('Y_m_d_His');
        $this->backupPath = $this->option('path') 
            ? $this->option('path') 
            : storage_path("app/backups_secure/{$this->date}");

        if ($this->option('dry-run')) {
            $this->warn('MODO SIMULACIÓN - No se realizarán cambios reales');
            $this->line('');
        }

        $startTime = microtime(true);

        try {
            // Verificar dependencias
            $this->checkDependencies();

            // Crear directorio de respaldo
            $this->createBackupDirectory();

            // Ejecutar respaldos según opciones
            $this->backupDatabase();
            $this->backupStorage();
            
            if ($this->option('full') || $this->config['backup_source_code_enabled']) {
                $this->backupSourceCode();
            }

            // Verificación y cifrado
            $this->verifyAndEncryptBackups();
            
            // Sincronización con nube
            $this->syncToCloud();
            
            // Generar reporte
            $this->generateReport();

            $duration = round(microtime(true) - $startTime, 2);
            
            $this->newLine();
            $this->info('Respaldo seguro completado exitosamente');
            $this->line("Ubicación: {$this->backupPath}");
            $this->line("Duración: {$duration} segundos");
            $this->line("Cifrado: " . ($this->config['encryption_enabled'] ? 'Activo' : 'Inactivo'));

            // Enviar notificación
            $this->sendNotification('SUCCESS', "Respaldo completado en {$duration}s");

            Log::info('Secure backup completed successfully', [
                'path' => $this->backupPath,
                'duration' => $duration,
                'encrypted' => $this->config['encryption_enabled']
            ]);

            return Command::SUCCESS;

        } catch (\Exception $e) {
            $this->error("❌ Error durante el respaldo: {$e->getMessage()}");
            
            $this->sendNotification('ERROR', $e->getMessage());
            
            Log::error('Secure backup failed', [
                'error' => $e->getMessage(),
                'path' => $this->backupPath
            ]);

            return Command::FAILURE;
        }
    }

    protected function loadConfiguration()
    {
        $envFile = base_path('.env.backup');
        
        if (!File::exists($envFile)) {
            throw new \Exception('Archivo .env.backup no encontrado. Copia .env.backup.example y configura las variables.');
        }

        // Leer configuración desde .env.backup
        $lines = file($envFile, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
        $this->config = [];

        foreach ($lines as $line) {
            if (str_contains($line, '=') && !str_starts_with($line, '#')) {
                [$key, $value] = explode('=', $line, 2);
                $this->config[strtolower($key)] = $value;
            }
        }

        // Valores por defecto
        $this->config = array_merge([
            'db_host' => '127.0.0.1',
            'db_port' => '3306',
            'db_database' => 'sistema2',
            'db_username' => 'root',
            'gpg_recipient' => 'backup@brisas.com',
            'encryption_enabled' => 'true',
            'rclone_remote' => '',
            'webhook_enabled' => 'false',
            'backup_source_code_enabled' => 'false',
            'backup_retention_days' => '30'
        ], $this->config);
    }

    protected function checkDependencies()
    {
        $this->info('Verificando dependencias...');
        
        $requiredTools = ['mysqldump', 'gpg', 'rclone'];
        $missing = [];

        foreach ($requiredTools as $tool) {
            $result = Process::run("which {$tool}");
            if ($result->failed()) {
                $missing[] = $tool;
            }
        }

        if (!empty($missing)) {
            throw new \Exception("Herramientas faltantes: " . implode(', ', $missing) . 
                ". Instala con: sudo apt-get install " . implode(' ', $missing));
        }

        $this->info('Todas las dependencias están disponibles');
    }

    protected function createBackupDirectory()
    {
        if (!$this->option('dry-run') && !File::exists($this->backupPath)) {
            File::makeDirectory($this->backupPath, 0755, true);
            $this->info("Directorio de respaldo creado: {$this->backupPath}");
        }
    }

    protected function backupDatabase()
    {
        $this->info('Respaldando base de datos...');
        
        $backupFile = "{$this->backupPath}/database_{$this->date}.sql.gz";
        $mysqlConfigFile = $this->createSecureMysqlConfig();

        $command = sprintf(
            'mysqldump --defaults-extra-file=%s --single-transaction --routines --triggers --events --hex-blob --default-character-set=utf8mb4 %s | gzip > %s',
            $mysqlConfigFile,
            $this->config['db_database'],
            $backupFile
        );

        if ($this->option('dry-run')) {
            $this->line("   🧪 Simulando: {$command}");
            return;
        }

        $result = Process::run($command);
        
        // Limpiar archivo de configuración temporal
        File::delete($mysqlConfigFile);

        if ($result->successful() && File::exists($backupFile)) {
            $size = $this->formatBytes(File::size($backupFile));
            $this->line("   ✅ Base de datos respaldada ({$size})");
        } else {
            throw new \Exception('Error al respaldar la base de datos: ' . $result->errorOutput());
        }
    }

    protected function createSecureMysqlConfig()
    {
        $configFile = storage_path("app/mysql_config_{$this->date}.cnf");
        
        $config = "[mysqldump]\n";
        $config .= "host={$this->config['db_host']}\n";
        $config .= "port={$this->config['db_port']}\n";
        $config .= "user={$this->config['db_username']}\n";
        $config .= "password={$this->config['db_password']}\n";

        File::put($configFile, $config);
        File::chmod($configFile, 0600); // Solo lectura/escritura para el propietario

        return $configFile;
    }

    protected function backupStorage()
    {
        $this->info('Respaldando archivos de storage...');
        
        $storagePath = storage_path('app');
        $backupFile = "{$this->backupPath}/storage_{$this->date}.tar.gz";

        if (!File::exists($storagePath)) {
            $this->warn('   ⚠️  Directorio storage/app no encontrado');
            return;
        }

        $command = sprintf(
            'tar --exclude="cache/*" --exclude="framework/cache/*" --exclude="framework/views/*" -czf %s -C %s .',
            $backupFile,
            $storagePath
        );

        if ($this->option('dry-run')) {
            $this->line("   🧪 Simulando: {$command}");
            return;
        }

        $result = Process::run($command);

        if ($result->successful() && File::exists($backupFile)) {
            $size = $this->formatBytes(File::size($backupFile));
            $this->line("   ✅ Storage respaldado ({$size})");
        } else {
            throw new \Exception('Error al respaldar storage: ' . $result->errorOutput());
        }
    }

    protected function backupSourceCode()
    {
        $this->info('Respaldando código fuente...');
        
        $backupFile = "{$this->backupPath}/source_{$this->date}.tar.gz";
        $projectPath = base_path();

        $command = sprintf(
            'tar --exclude="node_modules" --exclude="vendor" --exclude="storage/framework/cache" --exclude="storage/framework/views" --exclude="bootstrap/cache" --exclude=".git" --exclude="*.log" -czf %s -C %s .',
            $backupFile,
            $projectPath
        );

        if ($this->option('dry-run')) {
            $this->line("   🧪 Simulando: {$command}");
            return;
        }

        $result = Process::run($command);

        if ($result->successful() && File::exists($backupFile)) {
            $size = $this->formatBytes(File::size($backupFile));
            $this->line("   ✅ Código fuente respaldado ({$size})");
        } else {
            throw new \Exception('Error al respaldar código fuente: ' . $result->errorOutput());
        }
    }

    protected function verifyAndEncryptBackups()
    {
        $this->info('Verificando y cifrando respaldos...');
        
        $backupFiles = glob("{$this->backupPath}/*.{tar.gz,sql.gz}", GLOB_BRACE);
        $errors = 0;

        foreach ($backupFiles as $file) {
            if (!File::exists($file) || File::size($file) === 0) {
                $this->error("   ❌ Archivo inválido: " . basename($file));
                $errors++;
                continue;
            }

            // Cifrar archivo
            if ($this->config['encryption_enabled'] === 'true') {
                $encryptedFile = $file . '.gpg';
                
                $command = sprintf(
                    'gpg --batch --yes --trust-model always --encrypt --recipient %s --output %s %s',
                    $this->config['gpg_recipient'],
                    $encryptedFile,
                    $file
                );

                if ($this->option('dry-run')) {
                    $this->line("   🧪 Simulando cifrado: " . basename($file));
                    continue;
                }

                $result = Process::run($command);

                if ($result->successful() && File::exists($encryptedFile)) {
                    File::delete($file); // Eliminar original
                    $this->line("   ✅ " . basename($file) . " → " . basename($encryptedFile));
                } else {
                    $this->error("   ❌ Error cifrando: " . basename($file));
                    $errors++;
                }
            }
        }

        if ($errors > 0) {
            throw new \Exception("Se encontraron {$errors} errores durante la verificación/cifrado");
        }

        $this->info('Todos los respaldos verificados y cifrados');
    }

    protected function syncToCloud()
    {
        if (empty($this->config['rclone_remote'])) {
            $this->warn('   ⚠️  Rclone no configurado, omitiendo sincronización');
            return;
        }

        $this->info('Sincronizando con la nube...');
        
        $remotePath = $this->config['rclone_remote'] . ':backups/brisas/' . $this->date;
        
        $command = sprintf(
            'rclone sync %s %s --progress --transfers 4 --checkers 4',
            $this->backupPath,
            $remotePath
        );

        if ($this->option('dry-run')) {
            $this->line("   🧪 Simulando: {$command}");
            return;
        }

        $result = Process::run($command);

        if ($result->successful()) {
            $this->line('   ✅ Sincronización completada');
        } else {
            $this->warn('   ⚠️  Error en sincronización: ' . $result->errorOutput());
        }
    }

    protected function generateReport()
    {
        $this->info('Generando reporte...');
        
        $reportFile = "{$this->backupPath}/backup_report_{$this->date}.txt";
        
        $content = "========================================\n";
        $content .= "REPORTE DE RESPALDO SEGURO - BRISAS\n";
        $content .= "========================================\n";
        $content .= "Fecha: " . now()->format('Y-m-d H:i:s') . "\n";
        $content .= "Directorio: {$this->backupPath}\n";
        $content .= "Versión Laravel: " . app()->version() . "\n";
        $content .= "PHP Version: " . PHP_VERSION . "\n\n";
        
        $content .= "SEGURIDAD:\n";
        $content .= "🔒 Cifrado: " . ($this->config['encryption_enabled'] === 'true' ? 'Activo' : 'Inactivo') . "\n";
        $content .= "☁️  Nube: " . (!empty($this->config['rclone_remote']) ? $this->config['rclone_remote'] : 'No configurado') . "\n";
        $content .= "📱 Notificaciones: " . ($this->config['webhook_enabled'] === 'true' ? 'Activas' : 'Inactivas') . "\n\n";
        
        $content .= "ARCHIVOS RESPALDADOS:\n";
        
        $totalSize = 0;
        $backupFiles = glob("{$this->backupPath}/*");
        
        foreach ($backupFiles as $file) {
            if (is_file($file) && !str_contains(basename($file), 'report')) {
                $size = File::size($file);
                $totalSize += $size;
                $content .= "  " . basename($file) . " - " . $this->formatBytes($size) . "\n";
            }
        }
        
        $content .= "\nESPACIO TOTAL: " . $this->formatBytes($totalSize) . "\n";
        $content .= "ESTADO: COMPLETADO EXITOSAMENTE\n";
        $content .= "========================================\n";
        
        if (!$this->option('dry-run')) {
            File::put($reportFile, $content);
            $this->line("Reporte generado: " . basename($reportFile));
        }
    }

    protected function sendNotification($status, $message)
    {
        if ($this->config['webhook_enabled'] !== 'true' || empty($this->config['webhook_url'])) {
            return;
        }

        $payload = [
            'text' => "🔒 Brisas Backup {$status}: {$message}"
        ];

        try {
            Process::run(sprintf(
                'curl -X POST -H "Content-type: application/json" --data %s %s',
                escapeshellarg(json_encode($payload)),
                $this->config['webhook_url']
            ));
        } catch (\Exception $e) {
            // Silenciar errores de notificación para no interrumpir el respaldo
            Log::warning('Failed to send notification', ['error' => $e->getMessage()]);
        }
    }

    protected function formatBytes($bytes)
    {
        $units = ['B', 'KB', 'MB', 'GB'];
        $bytes = max($bytes, 0);
        $pow = floor(($bytes ? log($bytes) : 0) / log(1024));
        $pow = min($pow, count($units) - 1);
        
        $bytes /= pow(1024, $pow);
        
        return round($bytes, 2) . ' ' . $units[$pow];
    }
}
