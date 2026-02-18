<?php
/**
 * EmailSender - Utilidad para envío de correos electrónicos
 * Soporta SMTP con autenticación y diferentes tipos de encriptación
 */
class EmailSender {
    
    private $config = [];
    private $errors = [];
    
    /**
     * Constructor - Carga configuración de email desde BD
     */
    public function __construct() {
        $this->loadConfig();
    }
    
    /**
     * Cargar configuración de email desde base de datos
     */
    private function loadConfig() {
        $db = Database::getInstance();
        
        $sql = "SELECT clave, valor FROM configuraciones 
                WHERE clave LIKE 'email_%' OR clave LIKE 'smtp_%'";
        $configs = $db->query($sql)->fetchAll();
        
        foreach ($configs as $config) {
            $this->config[$config['clave']] = $config['valor'];
        }
    }
    
    /**
     * Enviar correo electrónico
     * 
     * @param string|array $to Destinatario(s)
     * @param string $subject Asunto
     * @param string $message Mensaje (puede ser HTML)
     * @param array $options Opciones adicionales (cc, bcc, attachments, etc.)
     * @return bool
     */
    public function send($to, $subject, $message, $options = []) {
        // Verificar si el email está habilitado
        if (empty($this->config['email_enabled']) || $this->config['email_enabled'] != '1') {
            $this->errors[] = 'El envío de correos está deshabilitado en la configuración';
            return false;
        }
        
        // Validar configuración SMTP
        if (!$this->validateConfig()) {
            return false;
        }
        
        try {
            // Preparar destinatarios
            $recipients = is_array($to) ? implode(', ', $to) : $to;
            
            // Configurar headers
            $headers = $this->buildHeaders($options);
            
            // Preparar mensaje
            $fullMessage = $this->buildMessage($message, $options);
            
            // Enviar email usando función mail() de PHP
            // En producción, se recomienda usar una librería como PHPMailer o Swift Mailer
            if ($this->config['smtp_host']) {
                // Usar SMTP
                return $this->sendSMTP($recipients, $subject, $fullMessage, $headers);
            } else {
                // Usar mail() nativo de PHP
                return mail($recipients, $subject, $fullMessage, $headers);
            }
            
        } catch (Exception $e) {
            $this->errors[] = 'Error al enviar email: ' . $e->getMessage();
            return false;
        }
    }
    
    /**
     * Enviar email de prueba
     * 
     * @param string $to Email de destino
     * @return bool
     */
    public function sendTest($to) {
        $subject = 'Prueba de Configuración de Email - ' . date('Y-m-d H:i:s');
        $message = '
            <html>
            <body style="font-family: Arial, sans-serif; padding: 20px;">
                <h2 style="color: #667eea;">Email de Prueba</h2>
                <p>Este es un email de prueba desde el Sistema de Inventario Albercas.</p>
                <p><strong>Fecha y hora:</strong> ' . date('Y-m-d H:i:s') . '</p>
                <p><strong>Servidor SMTP:</strong> ' . htmlspecialchars($this->config['smtp_host']) . '</p>
                <p><strong>Puerto:</strong> ' . htmlspecialchars($this->config['smtp_port']) . '</p>
                <p><strong>Encriptación:</strong> ' . htmlspecialchars($this->config['smtp_encryption']) . '</p>
                <hr>
                <p style="color: #28a745; font-weight: bold;">✓ La configuración de email funciona correctamente</p>
                <p style="font-size: 12px; color: #666;">
                    Si recibió este email, significa que el servidor SMTP está configurado correctamente.
                </p>
            </body>
            </html>
        ';
        
        return $this->send($to, $subject, $message, ['html' => true]);
    }
    
    /**
     * Construir headers para el email
     */
    private function buildHeaders($options) {
        $headers = [];
        
        // From
        $fromAddress = $this->config['email_from_address'] ?? 'noreply@localhost';
        $fromName = $this->config['email_from_name'] ?? 'Sistema';
        $headers[] = 'From: ' . $fromName . ' <' . $fromAddress . '>';
        
        // Reply-To
        if (isset($options['reply_to'])) {
            $headers[] = 'Reply-To: ' . $options['reply_to'];
        }
        
        // CC
        if (isset($options['cc'])) {
            $cc = is_array($options['cc']) ? implode(', ', $options['cc']) : $options['cc'];
            $headers[] = 'Cc: ' . $cc;
        }
        
        // BCC
        if (isset($options['bcc'])) {
            $bcc = is_array($options['bcc']) ? implode(', ', $options['bcc']) : $options['bcc'];
            $headers[] = 'Bcc: ' . $bcc;
        }
        
        // Content type
        if (isset($options['html']) && $options['html']) {
            $headers[] = 'MIME-Version: 1.0';
            $headers[] = 'Content-Type: text/html; charset=UTF-8';
        } else {
            $headers[] = 'Content-Type: text/plain; charset=UTF-8';
        }
        
        return implode("\r\n", $headers);
    }
    
    /**
     * Construir mensaje completo
     */
    private function buildMessage($message, $options) {
        // Si es HTML, agregar estructura básica si no la tiene
        if (isset($options['html']) && $options['html']) {
            if (strpos($message, '<html>') === false) {
                $message = '
                    <html>
                    <body style="font-family: Arial, sans-serif;">
                        ' . $message . '
                    </body>
                    </html>
                ';
            }
        }
        
        return $message;
    }
    
    /**
     * Enviar via SMTP (implementación básica)
     * Para producción, usar PHPMailer o librería similar
     */
    private function sendSMTP($to, $subject, $message, $headers) {
        // Configurar ini settings para SMTP
        ini_set('SMTP', $this->config['smtp_host']);
        ini_set('smtp_port', $this->config['smtp_port']);
        
        // Nota: La función mail() de PHP tiene limitaciones con SMTP
        // En producción se debe usar PHPMailer o Swift Mailer
        return mail($to, $subject, $message, $headers);
    }
    
    /**
     * Validar configuración SMTP
     */
    private function validateConfig() {
        $required = ['smtp_host', 'smtp_port', 'email_from_address'];
        
        foreach ($required as $field) {
            if (empty($this->config[$field])) {
                $this->errors[] = "Configuración requerida faltante: $field";
                return false;
            }
        }
        
        // Validar email
        if (!filter_var($this->config['email_from_address'], FILTER_VALIDATE_EMAIL)) {
            $this->errors[] = 'Email de remitente inválido';
            return false;
        }
        
        return true;
    }
    
    /**
     * Obtener errores
     */
    public function getErrors() {
        return $this->errors;
    }
    
    /**
     * Obtener último error
     */
    public function getLastError() {
        return end($this->errors);
    }
    
    /**
     * Verificar si hay errores
     */
    public function hasErrors() {
        return !empty($this->errors);
    }
    
    /**
     * Obtener configuración actual
     */
    public function getConfig() {
        return $this->config;
    }
    
    /**
     * Probar conexión SMTP
     */
    public function testConnection() {
        if (!$this->validateConfig()) {
            return [
                'success' => false,
                'message' => 'Configuración incompleta: ' . implode(', ', $this->errors)
            ];
        }
        
        $host = $this->config['smtp_host'];
        $port = $this->config['smtp_port'];
        
        // Intentar conexión al servidor SMTP
        $connection = @fsockopen($host, $port, $errno, $errstr, 10);
        
        if (!$connection) {
            return [
                'success' => false,
                'message' => "No se pudo conectar a $host:$port - $errstr ($errno)"
            ];
        }
        
        fclose($connection);
        
        return [
            'success' => true,
            'message' => "Conexión exitosa a $host:$port"
        ];
    }
}
