<?php
/**
 * DatabaseBackup - Utilidad para respaldo y restauración de base de datos
 */
class DatabaseBackup {
    
    private $db;
    private $backupDir;
    private $errors = [];
    
    /**
     * Constructor
     */
    public function __construct() {
        $this->db = Database::getInstance();
        
        // Obtener ruta de backup desde configuración
        $backupPath = ConfiguracionController::get('backup_path', '/backups');
        $this->backupDir = ROOT_PATH . $backupPath;
        
        // Crear directorio si no existe
        if (!is_dir($this->backupDir)) {
            mkdir($this->backupDir, 0755, true);
        }
    }
    
    /**
     * Crear respaldo completo de la base de datos
     * 
     * @param string $description Descripción del respaldo
     * @return array [success, filename, message]
     */
    public function create($description = '') {
        try {
            $timestamp = date('Y-m-d_His');
            $filename = "backup_inventario_albercas_$timestamp.sql";
            $filepath = $this->backupDir . '/' . $filename;
            
            // Obtener configuración de base de datos
            $dbHost = Config::get('DB_HOST', 'localhost');
            $dbName = Config::get('DB_NAME', 'inventario_albercas');
            $dbUser = Config::get('DB_USER', 'root');
            $dbPass = Config::get('DB_PASS', '');
            
            // Comando mysqldump
            $command = sprintf(
                'mysqldump --host=%s --user=%s %s %s > %s 2>&1',
                escapeshellarg($dbHost),
                escapeshellarg($dbUser),
                $dbPass ? '--password=' . escapeshellarg($dbPass) : '',
                escapeshellarg($dbName),
                escapeshellarg($filepath)
            );
            
            // Ejecutar backup
            exec($command, $output, $returnCode);
            
            if ($returnCode !== 0 || !file_exists($filepath) || filesize($filepath) === 0) {
                $this->errors[] = 'Error al ejecutar mysqldump: ' . implode("\n", $output);
                return [
                    'success' => false,
                    'message' => 'Error al crear respaldo: ' . implode("\n", $output)
                ];
            }
            
            // Comprimir el archivo
            $zipFilename = $filename . '.gz';
            $zipFilepath = $this->backupDir . '/' . $zipFilename;
            
            if (function_exists('gzencode')) {
                $sqlContent = file_get_contents($filepath);
                $compressed = gzencode($sqlContent, 9);
                file_put_contents($zipFilepath, $compressed);
                unlink($filepath); // Eliminar archivo sin comprimir
                $finalFile = $zipFilename;
                $finalSize = filesize($zipFilepath);
            } else {
                $finalFile = $filename;
                $finalSize = filesize($filepath);
            }
            
            // Registrar backup en la tabla de auditoría
            $usuario = Auth::user();
            $this->db->query(
                "INSERT INTO auditoria (usuario_id, accion, tabla, detalles, ip_address, user_agent) 
                 VALUES (:usuario_id, 'backup', 'database', :detalles, :ip, :ua)",
                [
                    'usuario_id' => $usuario['id'],
                    'detalles' => "Backup creado: $finalFile (" . $this->formatBytes($finalSize) . ") - $description",
                    'ip' => $_SERVER['REMOTE_ADDR'],
                    'ua' => $_SERVER['HTTP_USER_AGENT']
                ]
            );
            
            return [
                'success' => true,
                'filename' => $finalFile,
                'filepath' => $this->backupDir . '/' . $finalFile,
                'size' => $finalSize,
                'message' => "Respaldo creado exitosamente: $finalFile"
            ];
            
        } catch (Exception $e) {
            $this->errors[] = $e->getMessage();
            return [
                'success' => false,
                'message' => 'Error: ' . $e->getMessage()
            ];
        }
    }
    
    /**
     * Restaurar base de datos desde un backup
     * 
     * @param string $filename Nombre del archivo de backup
     * @return array [success, message]
     */
    public function restore($filename) {
        try {
            $filepath = $this->backupDir . '/' . $filename;
            
            if (!file_exists($filepath)) {
                return [
                    'success' => false,
                    'message' => 'Archivo de respaldo no encontrado'
                ];
            }
            
            // Descomprimir si es necesario
            $sqlFile = $filepath;
            if (substr($filename, -3) === '.gz') {
                $sqlContent = gzdecode(file_get_contents($filepath));
                $sqlFile = $this->backupDir . '/temp_restore.sql';
                file_put_contents($sqlFile, $sqlContent);
            }
            
            // Obtener configuración de base de datos
            $dbHost = Config::get('DB_HOST', 'localhost');
            $dbName = Config::get('DB_NAME', 'inventario_albercas');
            $dbUser = Config::get('DB_USER', 'root');
            $dbPass = Config::get('DB_PASS', '');
            
            // Comando mysql para restaurar
            $command = sprintf(
                'mysql --host=%s --user=%s %s %s < %s 2>&1',
                escapeshellarg($dbHost),
                escapeshellarg($dbUser),
                $dbPass ? '--password=' . escapeshellarg($dbPass) : '',
                escapeshellarg($dbName),
                escapeshellarg($sqlFile)
            );
            
            // Ejecutar restore
            exec($command, $output, $returnCode);
            
            // Limpiar archivo temporal
            if ($sqlFile !== $filepath && file_exists($sqlFile)) {
                unlink($sqlFile);
            }
            
            if ($returnCode !== 0) {
                return [
                    'success' => false,
                    'message' => 'Error al restaurar: ' . implode("\n", $output)
                ];
            }
            
            // Registrar en auditoría
            $usuario = Auth::user();
            $this->db->query(
                "INSERT INTO auditoria (usuario_id, accion, tabla, detalles, ip_address, user_agent) 
                 VALUES (:usuario_id, 'restore', 'database', :detalles, :ip, :ua)",
                [
                    'usuario_id' => $usuario['id'],
                    'detalles' => "Base de datos restaurada desde: $filename",
                    'ip' => $_SERVER['REMOTE_ADDR'],
                    'ua' => $_SERVER['HTTP_USER_AGENT']
                ]
            );
            
            return [
                'success' => true,
                'message' => 'Base de datos restaurada exitosamente'
            ];
            
        } catch (Exception $e) {
            return [
                'success' => false,
                'message' => 'Error: ' . $e->getMessage()
            ];
        }
    }
    
    /**
     * Listar todos los backups disponibles
     * 
     * @return array Lista de backups con metadata
     */
    public function listBackups() {
        $backups = [];
        
        if (!is_dir($this->backupDir)) {
            return $backups;
        }
        
        $files = glob($this->backupDir . '/backup_*.{sql,sql.gz}', GLOB_BRACE);
        
        foreach ($files as $file) {
            $filename = basename($file);
            $backups[] = [
                'filename' => $filename,
                'filepath' => $file,
                'size' => filesize($file),
                'size_formatted' => $this->formatBytes(filesize($file)),
                'date' => date('Y-m-d H:i:s', filemtime($file)),
                'timestamp' => filemtime($file)
            ];
        }
        
        // Ordenar por fecha descendente
        usort($backups, function($a, $b) {
            return $b['timestamp'] - $a['timestamp'];
        });
        
        return $backups;
    }
    
    /**
     * Eliminar un backup
     * 
     * @param string $filename Nombre del archivo
     * @return array [success, message]
     */
    public function delete($filename) {
        try {
            $filepath = $this->backupDir . '/' . $filename;
            
            // Validar que el archivo esté en el directorio de backups
            if (strpos(realpath($filepath), realpath($this->backupDir)) !== 0) {
                return [
                    'success' => false,
                    'message' => 'Archivo no válido'
                ];
            }
            
            if (!file_exists($filepath)) {
                return [
                    'success' => false,
                    'message' => 'Archivo no encontrado'
                ];
            }
            
            unlink($filepath);
            
            // Registrar en auditoría
            $usuario = Auth::user();
            $this->db->query(
                "INSERT INTO auditoria (usuario_id, accion, tabla, detalles, ip_address, user_agent) 
                 VALUES (:usuario_id, 'eliminar', 'database_backup', :detalles, :ip, :ua)",
                [
                    'usuario_id' => $usuario['id'],
                    'detalles' => "Backup eliminado: $filename",
                    'ip' => $_SERVER['REMOTE_ADDR'],
                    'ua' => $_SERVER['HTTP_USER_AGENT']
                ]
            );
            
            return [
                'success' => true,
                'message' => 'Respaldo eliminado correctamente'
            ];
            
        } catch (Exception $e) {
            return [
                'success' => false,
                'message' => 'Error: ' . $e->getMessage()
            ];
        }
    }
    
    /**
     * Descargar un backup
     * 
     * @param string $filename Nombre del archivo
     */
    public function download($filename) {
        $filepath = $this->backupDir . '/' . $filename;
        
        // Validar que el archivo esté en el directorio de backups
        if (strpos(realpath($filepath), realpath($this->backupDir)) !== 0) {
            die('Archivo no válido');
        }
        
        if (!file_exists($filepath)) {
            die('Archivo no encontrado');
        }
        
        // Registrar en auditoría
        $usuario = Auth::user();
        $this->db->query(
            "INSERT INTO auditoria (usuario_id, accion, tabla, detalles, ip_address, user_agent) 
             VALUES (:usuario_id, 'descargar', 'database_backup', :detalles, :ip, :ua)",
            [
                'usuario_id' => $usuario['id'],
                'detalles' => "Backup descargado: $filename",
                'ip' => $_SERVER['REMOTE_ADDR'],
                'ua' => $_SERVER['HTTP_USER_AGENT']
            ]
        );
        
        // Enviar headers de descarga
        header('Content-Type: application/octet-stream');
        header('Content-Disposition: attachment; filename="' . $filename . '"');
        header('Content-Length: ' . filesize($filepath));
        header('Cache-Control: no-cache, must-revalidate');
        
        readfile($filepath);
        exit;
    }
    
    /**
     * Limpiar backups antiguos según política de retención
     * 
     * @return array [success, deleted_count, message]
     */
    public function cleanOldBackups() {
        $retentionDays = (int)ConfiguracionController::get('backup_retention_days', 30);
        $cutoffTimestamp = time() - ($retentionDays * 24 * 60 * 60);
        
        $backups = $this->listBackups();
        $deletedCount = 0;
        
        foreach ($backups as $backup) {
            if ($backup['timestamp'] < $cutoffTimestamp) {
                $result = $this->delete($backup['filename']);
                if ($result['success']) {
                    $deletedCount++;
                }
            }
        }
        
        return [
            'success' => true,
            'deleted_count' => $deletedCount,
            'message' => "Se eliminaron $deletedCount respaldos antiguos"
        ];
    }
    
    /**
     * Formatear bytes a formato legible
     */
    private function formatBytes($bytes, $precision = 2) {
        $units = ['B', 'KB', 'MB', 'GB', 'TB'];
        
        for ($i = 0; $bytes > 1024 && $i < count($units) - 1; $i++) {
            $bytes /= 1024;
        }
        
        return round($bytes, $precision) . ' ' . $units[$i];
    }
    
    /**
     * Obtener errores
     */
    public function getErrors() {
        return $this->errors;
    }
    
    /**
     * Verificar si mysqldump está disponible
     */
    public static function isMysqldumpAvailable() {
        exec('which mysqldump 2>&1', $output, $returnCode);
        return $returnCode === 0;
    }
    
    /**
     * Verificar si mysql client está disponible
     */
    public static function isMysqlAvailable() {
        exec('which mysql 2>&1', $output, $returnCode);
        return $returnCode === 0;
    }
}
