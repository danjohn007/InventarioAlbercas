<?php
// Helper: get config value safely
function cfgVal($config_all, $key, $default = '') {
    return isset($config_all[$key]) ? htmlspecialchars($config_all[$key], ENT_QUOTES, 'UTF-8') : htmlspecialchars($default, ENT_QUOTES, 'UTF-8');
}
$csrf = htmlspecialchars($_SESSION['csrf_token'] ?? '', ENT_QUOTES, 'UTF-8');
?>
<style>
.settings-sidebar .nav-link {
    color: #495057;
    border-radius: 8px;
    padding: 10px 15px;
    margin-bottom: 4px;
    font-size: 0.9rem;
    display: flex;
    align-items: center;
    gap: 10px;
    transition: all 0.2s;
}
.settings-sidebar .nav-link:hover {
    background: rgba(102,126,234,0.08);
    color: #667eea;
}
.settings-sidebar .nav-link.active {
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    color: #fff !important;
    font-weight: 600;
    box-shadow: 0 4px 15px rgba(102,126,234,0.3);
}
.tab-pane-header {
    border-bottom: 2px solid #f0f0f0;
    padding-bottom: 15px;
    margin-bottom: 25px;
}
.tab-pane-header h4 { margin-bottom: 4px; font-weight: 700; }
.tab-pane-header p  { color: #6c757d; margin: 0; font-size: 0.9rem; }
.section-subtitle {
    font-size: 0.85rem;
    font-weight: 600;
    text-transform: uppercase;
    letter-spacing: 0.5px;
    color: #667eea;
    margin-bottom: 12px;
    padding-bottom: 6px;
    border-bottom: 1px solid #e9ecef;
}
.form-label { font-weight: 600; font-size: 0.875rem; }
.form-text  { font-size: 0.78rem; }
.settings-card {
    background: #fff;
    border-radius: 12px;
    padding: 24px;
    box-shadow: 0 2px 15px rgba(0,0,0,0.06);
}
.iot-device-card {
    border: 1px solid #dee2e6;
    border-radius: 10px;
    padding: 20px;
    margin-bottom: 20px;
}
.iot-device-card .device-header {
    display: flex;
    align-items: center;
    gap: 10px;
    margin-bottom: 16px;
    font-weight: 700;
    font-size: 1rem;
}
</style>

<!-- Page Header -->
<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h2 class="mb-1"><i class="bi bi-gear-wide-connected me-2"></i>Configuraciones Globales</h2>
        <p class="text-muted mb-0">Administra todas las configuraciones del sistema</p>
    </div>
</div>

<?php if (isset($_SESSION['success'])): ?>
<div class="alert alert-success alert-dismissible fade show">
    <i class="bi bi-check-circle me-2"></i><?php echo htmlspecialchars($_SESSION['success'], ENT_QUOTES, 'UTF-8'); unset($_SESSION['success']); ?>
    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
</div>
<?php endif; ?>
<?php if (isset($_SESSION['error'])): ?>
<div class="alert alert-danger alert-dismissible fade show">
    <i class="bi bi-exclamation-triangle me-2"></i><?php echo htmlspecialchars($_SESSION['error'], ENT_QUOTES, 'UTF-8'); unset($_SESSION['error']); ?>
    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
</div>
<?php endif; ?>

<div class="row g-4">
    <!-- Sidebar Navigation -->
    <div class="col-lg-3 col-md-4">
        <div class="settings-card p-3">
            <div class="nav flex-column settings-sidebar" id="settingsTabs" role="tablist">
                <a class="nav-link active" data-bs-toggle="pill" href="#tab-sitio" role="tab">
                    <i class="bi bi-globe2"></i> Sitio &amp; Logotipo
                </a>
                <a class="nav-link" data-bs-toggle="pill" href="#tab-correo" role="tab">
                    <i class="bi bi-envelope-at"></i> Correo Electr&oacute;nico
                </a>
                <a class="nav-link" data-bs-toggle="pill" href="#tab-contacto" role="tab">
                    <i class="bi bi-telephone-fill"></i> Contacto &amp; Horarios
                </a>
                <a class="nav-link" data-bs-toggle="pill" href="#tab-apariencia" role="tab">
                    <i class="bi bi-palette2"></i> Estilos de Color
                </a>
                <a class="nav-link" data-bs-toggle="pill" href="#tab-paypal" role="tab">
                    <i class="bi bi-paypal"></i> Cuenta PayPal
                </a>
                <a class="nav-link" data-bs-toggle="pill" href="#tab-qr" role="tab">
                    <i class="bi bi-qr-code"></i> API Generaci&oacute;n QR
                </a>
                <a class="nav-link" data-bs-toggle="pill" href="#tab-iot" role="tab">
                    <i class="bi bi-cpu"></i> Dispositivos IoT
                </a>
                <hr class="my-2">
                <a class="nav-link" data-bs-toggle="pill" href="#tab-bitacora" role="tab">
                    <i class="bi bi-journal-text"></i> Bit&aacute;cora de Acciones
                </a>
                <a class="nav-link" data-bs-toggle="pill" href="#tab-errores" role="tab">
                    <i class="bi bi-exclamation-octagon"></i> Registro de Errores
                </a>
                <a class="nav-link" data-bs-toggle="pill" href="#tab-chatbot" role="tab">
                    <i class="bi bi-whatsapp"></i> Chatbot WhatsApp
                </a>
            </div>
        </div>
    </div>

    <!-- Tab Content -->
    <div class="col-lg-9 col-md-8">
        <form action="<?php echo BASE_URL; ?>configuraciones/actualizar" method="POST" enctype="multipart/form-data" id="configForm">
        <input type="hidden" name="csrf_token" value="<?php echo $csrf; ?>">

        <div class="tab-content settings-card" id="settingsTabContent">

            <!-- TAB 1: SITIO -->
            <div class="tab-pane fade show active" id="tab-sitio" role="tabpanel">
                <div class="tab-pane-header">
                    <h4><i class="bi bi-globe2 me-2 text-primary"></i>Nombre del Sitio y Logotipo</h4>
                    <p>Configura la identidad visual del sistema.</p>
                </div>
                <div class="row g-3">
                    <div class="col-md-8">
                        <label class="form-label">Nombre del Sitio</label>
                        <input type="text" class="form-control" name="sitio_nombre"
                               value="<?php echo cfgVal($config_all, 'sitio_nombre', 'Sistema Inventario Albercas'); ?>"
                               placeholder="Ej: Mi Sistema de Albercas">
                        <div class="form-text">Aparece en la barra lateral y en el t&iacute;tulo del navegador.</div>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">Logotipo</label>
                        <?php $logoVal = $config_all['sitio_logo'] ?? ''; /* already sanitized by cfgVal pattern */ ?>
                        <?php if (!empty($logoVal)): ?>
                        <div class="mb-2">
                            <img src="<?php echo BASE_URL . htmlspecialchars($logoVal, ENT_QUOTES, 'UTF-8'); ?>"
                                 alt="Logo actual" style="max-height:60px;max-width:160px;object-fit:contain;border:1px solid #dee2e6;border-radius:6px;padding:5px;">
                        </div>
                        <?php endif; ?>
                        <input type="file" class="form-control" name="sitio_logo"
                               accept="image/png,image/jpeg,image/gif,image/webp" id="logoInput">
                        <input type="hidden" name="sitio_logo_current"
                               value="<?php echo htmlspecialchars($logoVal, ENT_QUOTES, 'UTF-8'); ?>">
                        <div class="form-text">PNG, JPG o WEBP. M&aacute;x 2 MB.</div>
                    </div>
                    <div class="col-12">
                        <label class="form-label">Descripci&oacute;n del Sitio</label>
                        <textarea class="form-control" name="sitio_descripcion" rows="2"
                                  placeholder="Descripci&oacute;n breve del sistema"><?php echo cfgVal($config_all, 'sitio_descripcion'); ?></textarea>
                    </div>
                </div>
            </div>

            <!-- TAB 2: CORREO -->
            <div class="tab-pane fade" id="tab-correo" role="tabpanel">
                <div class="tab-pane-header">
                    <h4><i class="bi bi-envelope-at me-2 text-primary"></i>Correo Electr&oacute;nico Principal</h4>
                    <p>Configura el servidor SMTP que env&iacute;a mensajes del sistema.</p>
                </div>
                <div class="mb-3">
                    <div class="form-check form-switch">
                        <input class="form-check-input" type="checkbox" name="email_enabled" id="emailEnabled"
                               value="1" <?php echo ($config_all['email_enabled'] ?? '0') == '1' ? 'checked' : ''; ?>>
                        <label class="form-check-label fw-bold" for="emailEnabled">Activar env&iacute;o de correos electr&oacute;nicos</label>
                    </div>
                </div>
                <p class="section-subtitle"><i class="bi bi-server me-1"></i>Configuraci&oacute;n SMTP</p>
                <div class="row g-3">
                    <div class="col-md-8">
                        <label class="form-label">Servidor SMTP</label>
                        <input type="text" class="form-control" name="smtp_host"
                               value="<?php echo cfgVal($config_all, 'smtp_host'); ?>"
                               placeholder="smtp.gmail.com">
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">Puerto SMTP</label>
                        <input type="number" class="form-control" name="smtp_port"
                               value="<?php echo cfgVal($config_all, 'smtp_port', '587'); ?>">
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">Encriptaci&oacute;n</label>
                        <select class="form-select" name="smtp_encryption">
                            <option value="tls"  <?php echo ($config_all['smtp_encryption'] ?? 'tls') == 'tls'  ? 'selected' : ''; ?>>TLS (recomendado)</option>
                            <option value="ssl"  <?php echo ($config_all['smtp_encryption'] ?? '') == 'ssl'  ? 'selected' : ''; ?>>SSL</option>
                            <option value="none" <?php echo ($config_all['smtp_encryption'] ?? '') == 'none' ? 'selected' : ''; ?>>Sin encriptaci&oacute;n</option>
                        </select>
                    </div>
                    <div class="col-md-8">
                        <label class="form-label">Usuario / Email SMTP</label>
                        <input type="text" class="form-control" name="smtp_username"
                               value="<?php echo cfgVal($config_all, 'smtp_username'); ?>"
                               placeholder="tu@correo.com">
                    </div>
                    <div class="col-12">
                        <label class="form-label">Contrase&ntilde;a SMTP</label>
                        <input type="password" class="form-control" name="smtp_password"
                               value="<?php echo cfgVal($config_all, 'smtp_password'); ?>">
                    </div>
                </div>
                <p class="section-subtitle mt-4"><i class="bi bi-person-badge me-1"></i>Remitente</p>
                <div class="row g-3">
                    <div class="col-md-6">
                        <label class="form-label">Email remitente (From)</label>
                        <input type="email" class="form-control" name="email_from_address"
                               value="<?php echo cfgVal($config_all, 'email_from_address'); ?>"
                               placeholder="noreply@miempresa.com">
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Nombre remitente</label>
                        <input type="text" class="form-control" name="email_from_name"
                               value="<?php echo cfgVal($config_all, 'email_from_name', 'Sistema Inventario Albercas'); ?>">
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Email del administrador</label>
                        <input type="email" class="form-control" name="email_admin"
                               value="<?php echo cfgVal($config_all, 'email_admin', 'admin@albercas.com'); ?>">
                        <div class="form-text">Recibe las notificaciones del sistema.</div>
                    </div>
                </div>
                <div class="alert alert-info mt-4 d-flex align-items-start gap-3">
                    <i class="bi bi-send-check fs-4 flex-shrink-0"></i>
                    <div class="w-100">
                        <strong>Probar configuraci&oacute;n:</strong> Guarda los cambios y env&iacute;a un correo de prueba.
                        <div class="input-group mt-2" style="max-width:420px;">
                            <input type="email" class="form-control" id="test_email_address"
                                   placeholder="destino@correo.com">
                            <button type="button" class="btn btn-primary" id="btnTestEmail">
                                <i class="bi bi-send me-1"></i>Enviar prueba
                            </button>
                        </div>
                        <div id="email_test_result" class="mt-2"></div>
                    </div>
                </div>
            </div>

            <!-- TAB 3: CONTACTO -->
            <div class="tab-pane fade" id="tab-contacto" role="tabpanel">
                <div class="tab-pane-header">
                    <h4><i class="bi bi-telephone-fill me-2 text-primary"></i>Tel&eacute;fonos de Contacto y Horarios</h4>
                    <p>Define los tel&eacute;fonos y horarios de atenci&oacute;n al cliente.</p>
                </div>
                <p class="section-subtitle"><i class="bi bi-telephone me-1"></i>Tel&eacute;fonos</p>
                <div class="row g-3">
                    <div class="col-md-6">
                        <label class="form-label">Tel&eacute;fono Principal</label>
                        <div class="input-group">
                            <span class="input-group-text"><i class="bi bi-telephone"></i></span>
                            <input type="text" class="form-control" name="telefono_principal"
                                   value="<?php echo cfgVal($config_all, 'telefono_principal'); ?>"
                                   placeholder="+52 55 1234 5678">
                        </div>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">WhatsApp</label>
                        <div class="input-group">
                            <span class="input-group-text"><i class="bi bi-whatsapp"></i></span>
                            <input type="text" class="form-control" name="telefono_whatsapp"
                                   value="<?php echo cfgVal($config_all, 'telefono_whatsapp'); ?>"
                                   placeholder="+52 55 1234 5678">
                        </div>
                        <div class="form-text">N&uacute;mero de WhatsApp Business para contacto con clientes.</div>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Tel&eacute;fono de Emergencias</label>
                        <div class="input-group">
                            <span class="input-group-text"><i class="bi bi-telephone-plus"></i></span>
                            <input type="text" class="form-control" name="telefono_emergencias"
                                   value="<?php echo cfgVal($config_all, 'telefono_emergencias'); ?>"
                                   placeholder="+52 55 9876 5432">
                        </div>
                        <div class="form-text">Disponible las 24 horas.</div>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Direcci&oacute;n</label>
                        <input type="text" class="form-control" name="direccion_contacto"
                               value="<?php echo cfgVal($config_all, 'direccion_contacto'); ?>"
                               placeholder="Calle, N&uacute;mero, Ciudad, Estado">
                    </div>
                </div>
                <p class="section-subtitle mt-4"><i class="bi bi-clock me-1"></i>Horarios de Atenci&oacute;n</p>
                <div class="row g-3">
                    <div class="col-md-4">
                        <label class="form-label">Lunes a Viernes</label>
                        <input type="text" class="form-control" name="horario_lunes_viernes"
                               value="<?php echo cfgVal($config_all, 'horario_lunes_viernes', '09:00 - 18:00'); ?>"
                               placeholder="09:00 - 18:00">
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">S&aacute;bado</label>
                        <input type="text" class="form-control" name="horario_sabado"
                               value="<?php echo cfgVal($config_all, 'horario_sabado', '09:00 - 14:00'); ?>"
                               placeholder="09:00 - 14:00">
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">Domingo</label>
                        <input type="text" class="form-control" name="horario_domingo"
                               value="<?php echo cfgVal($config_all, 'horario_domingo', 'Cerrado'); ?>"
                               placeholder="Cerrado">
                    </div>
                </div>
            </div>

            <!-- TAB 4: APARIENCIA -->
            <div class="tab-pane fade" id="tab-apariencia" role="tabpanel">
                <div class="tab-pane-header">
                    <h4><i class="bi bi-palette2 me-2 text-primary"></i>Estilos Principales de Color</h4>
                    <p>Personaliza los colores del sistema. Los cambios se aplican al recargar la p&aacute;gina.</p>
                </div>
                <div class="row g-4">
                    <div class="col-md-6">
                        <label class="form-label">Color Primario</label>
                        <div class="d-flex align-items-center gap-3">
                            <input type="color" class="form-control form-control-color" id="colorPrimario"
                                   name="color_primario"
                                   value="<?php echo cfgVal($config_all, 'color_primario', '#667eea'); ?>"
                                   style="width:70px;height:50px;">
                            <input type="text" class="form-control" id="colorPrimarioText"
                                   value="<?php echo cfgVal($config_all, 'color_primario', '#667eea'); ?>"
                                   style="max-width:120px;">
                        </div>
                        <div class="form-text">Color del inicio del degradado (barra lateral, botones).</div>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Color Secundario</label>
                        <div class="d-flex align-items-center gap-3">
                            <input type="color" class="form-control form-control-color" id="colorSecundario"
                                   name="color_secundario"
                                   value="<?php echo cfgVal($config_all, 'color_secundario', '#764ba2'); ?>"
                                   style="width:70px;height:50px;">
                            <input type="text" class="form-control" id="colorSecundarioText"
                                   value="<?php echo cfgVal($config_all, 'color_secundario', '#764ba2'); ?>"
                                   style="max-width:120px;">
                        </div>
                        <div class="form-text">Color del fin del degradado en encabezados y accents.</div>
                    </div>
                    <div class="col-12">
                        <label class="form-label d-block">Vista Previa del Degradado</label>
                        <div id="colorPreview" style="height:50px;border-radius:8px;background:linear-gradient(135deg,<?php echo cfgVal($config_all, 'color_primario', '#667eea'); ?> 0%,<?php echo cfgVal($config_all, 'color_secundario', '#764ba2'); ?> 100%);"></div>
                    </div>
                </div>
            </div>

            <!-- TAB 5: PAYPAL -->
            <div class="tab-pane fade" id="tab-paypal" role="tabpanel">
                <div class="tab-pane-header">
                    <h4><i class="bi bi-paypal me-2 text-primary"></i>Cuenta de PayPal Principal</h4>
                    <p>Configura la cuenta de PayPal del sistema para procesar pagos.</p>
                </div>
                <div class="row g-3 mb-3">
                    <div class="col-md-4">
                        <label class="form-label">Modo</label>
                        <select class="form-select" name="paypal_mode">
                            <option value="sandbox" <?php echo ($config_all['paypal_mode'] ?? 'sandbox') == 'sandbox' ? 'selected' : ''; ?>>Sandbox (pruebas)</option>
                            <option value="live"    <?php echo ($config_all['paypal_mode'] ?? '') == 'live' ? 'selected' : ''; ?>>Live (producci&oacute;n)</option>
                        </select>
                        <div class="form-text">Usa Sandbox para pruebas antes de activar en producci&oacute;n.</div>
                    </div>
                    <div class="col-md-8">
                        <label class="form-label">Email de la cuenta PayPal</label>
                        <input type="email" class="form-control" name="paypal_email"
                               value="<?php echo cfgVal($config_all, 'paypal_email'); ?>"
                               placeholder="tu@paypal.com">
                    </div>
                </div>
                <p class="section-subtitle"><i class="bi bi-key me-1"></i>Credenciales de API</p>
                <div class="alert alert-warning py-2">
                    <i class="bi bi-shield-lock me-2"></i>
                    <small>Obtén tus credenciales en <strong>developer.paypal.com</strong> &rarr; My Apps &amp; Credentials.</small>
                </div>
                <div class="row g-3">
                    <div class="col-12">
                        <label class="form-label">Client ID</label>
                        <input type="text" class="form-control font-monospace" name="paypal_client_id"
                               value="<?php echo cfgVal($config_all, 'paypal_client_id'); ?>"
                               placeholder="AXxxxxx...">
                    </div>
                    <div class="col-12">
                        <label class="form-label">Secret</label>
                        <input type="password" class="form-control font-monospace" name="paypal_secret"
                               value="<?php echo cfgVal($config_all, 'paypal_secret'); ?>"
                               placeholder="EK-xxxxx...">
                    </div>
                </div>
            </div>

            <!-- TAB 6: QR API -->
            <div class="tab-pane fade" id="tab-qr" role="tabpanel">
                <div class="tab-pane-header">
                    <h4><i class="bi bi-qr-code me-2 text-primary"></i>API para Creaci&oacute;n Masiva de QR</h4>
                    <p>Configura la API que permite generar c&oacute;digos QR de forma masiva.</p>
                </div>
                <div class="row g-3">
                    <div class="col-md-6">
                        <label class="form-label">Proveedor de API QR</label>
                        <select class="form-select" name="qr_api_provider">
                            <?php $qrProv = $config_all['qr_api_provider'] ?? ''; ?>
                            <option value=""          <?php echo $qrProv == ''          ? 'selected' : ''; ?>>-- Seleccionar --</option>
                            <option value="goqr"      <?php echo $qrProv == 'goqr'      ? 'selected' : ''; ?>>GoQR.me (api.qrserver.com)</option>
                            <option value="qrcodegen" <?php echo $qrProv == 'qrcodegen' ? 'selected' : ''; ?>>QRCode Generator API</option>
                            <option value="custom"    <?php echo $qrProv == 'custom'    ? 'selected' : ''; ?>>API Personalizada</option>
                        </select>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Clave API (API Key)</label>
                        <input type="text" class="form-control font-monospace" name="qr_api_key"
                               value="<?php echo cfgVal($config_all, 'qr_api_key'); ?>"
                               placeholder="sk-xxxxx...">
                        <div class="form-text">Deja en blanco para APIs p&uacute;blicas que no requieran autenticaci&oacute;n.</div>
                    </div>
                    <div class="col-12">
                        <label class="form-label">URL del Endpoint</label>
                        <input type="url" class="form-control font-monospace" name="qr_api_url"
                               value="<?php echo cfgVal($config_all, 'qr_api_url'); ?>"
                               placeholder="https://api.qrserver.com/v1/create-qr-code/">
                        <div class="form-text">URL del endpoint de la API para generar los c&oacute;digos QR.</div>
                    </div>
                </div>
            </div>

            <!-- TAB 7: IoT -->
            <div class="tab-pane fade" id="tab-iot" role="tabpanel">
                <div class="tab-pane-header">
                    <h4><i class="bi bi-cpu me-2 text-primary"></i>Dispositivos IoT</h4>
                    <p>Gesti&oacute;n de dispositivos Shelly Cloud y HikVision conectados al sistema.</p>
                </div>

                <div class="iot-device-card">
                    <div class="device-header">
                        <i class="bi bi-lightning-charge-fill text-warning fs-5"></i>
                        Shelly Cloud
                        <span class="badge bg-light text-dark fw-normal ms-1">Automatizaci&oacute;n inteligente</span>
                    </div>
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label">ID de cuenta Shelly</label>
                            <input type="text" class="form-control" name="shelly_account_id"
                                   value="<?php echo cfgVal($config_all, 'shelly_account_id'); ?>"
                                   placeholder="your_account_id">
                            <div class="form-text">Visible en my.shelly.cloud &rarr; Configuraci&oacute;n de cuenta.</div>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">API Key</label>
                            <input type="password" class="form-control font-monospace" name="shelly_api_key"
                                   value="<?php echo cfgVal($config_all, 'shelly_api_key'); ?>"
                                   placeholder="...">
                            <div class="form-text">Genera tu API key en shelly.cloud &rarr; Seguridad.</div>
                        </div>
                        <div class="col-12">
                            <label class="form-label">URL del servidor Shelly Cloud</label>
                            <input type="url" class="form-control font-monospace" name="shelly_api_url"
                                   value="<?php echo cfgVal($config_all, 'shelly_api_url', 'https://shelly-12-eu.shelly.cloud'); ?>">
                            <div class="form-text">Servidor asignado a tu regi&oacute;n (ver panel de Shelly Cloud).</div>
                        </div>
                    </div>
                </div>

                <div class="iot-device-card">
                    <div class="device-header">
                        <i class="bi bi-camera-video-fill text-info fs-5"></i>
                        HikVision
                        <span class="badge bg-light text-dark fw-normal ms-1">Videovigilancia</span>
                    </div>
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label">Direcci&oacute;n IP del Dispositivo</label>
                            <div class="input-group">
                                <span class="input-group-text"><i class="bi bi-hdd-network"></i></span>
                                <input type="text" class="form-control font-monospace" name="hikvision_device_ip"
                                       value="<?php echo cfgVal($config_all, 'hikvision_device_ip'); ?>"
                                       placeholder="192.168.1.100">
                            </div>
                            <div class="form-text">IP local o p&uacute;blica del NVR/DVR HikVision.</div>
                        </div>
                        <div class="col-md-3">
                            <label class="form-label">Usuario</label>
                            <input type="text" class="form-control" name="hikvision_username"
                                   value="<?php echo cfgVal($config_all, 'hikvision_username', 'admin'); ?>">
                        </div>
                        <div class="col-md-3">
                            <label class="form-label">Contrase&ntilde;a</label>
                            <input type="password" class="form-control" name="hikvision_password"
                                   value="<?php echo cfgVal($config_all, 'hikvision_password'); ?>">
                        </div>
                    </div>
                </div>
            </div>

            <!-- TAB 8: BITACORA -->
            <div class="tab-pane fade" id="tab-bitacora" role="tabpanel">
                <div class="tab-pane-header">
                    <h4><i class="bi bi-journal-text me-2 text-primary"></i>Bit&aacute;cora de Acciones</h4>
                    <p>Registro de todas las acciones realizadas en el sistema.</p>
                </div>
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <span class="text-muted small">&Uacute;ltimas 10 acciones registradas</span>
                    <a href="<?php echo BASE_URL; ?>configuraciones/auditoria" class="btn btn-sm btn-primary">
                        <i class="bi bi-list-ul me-1"></i>Ver historial completo
                    </a>
                </div>
                <?php if (!empty($auditLogs)): ?>
                <div class="table-responsive">
                    <table class="table table-sm table-hover align-middle">
                        <thead class="table-light">
                            <tr>
                                <th>Fecha</th>
                                <th>Usuario</th>
                                <th>Acci&oacute;n</th>
                                <th>M&oacute;dulo</th>
                                <th>Detalle</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($auditLogs as $log): ?>
                            <tr>
                                <td class="text-muted small"><?php echo htmlspecialchars(date('d/m/Y H:i', strtotime($log['fecha_creacion'])), ENT_QUOTES, 'UTF-8'); ?></td>
                                <td>
                                    <small class="fw-semibold"><?php echo htmlspecialchars($log['nombre'] ?? 'Sistema', ENT_QUOTES, 'UTF-8'); ?></small><br>
                                    <small class="text-muted"><?php echo htmlspecialchars($log['usuario'] ?? '', ENT_QUOTES, 'UTF-8'); ?></small>
                                </td>
                                <td>
                                    <?php
                                    $ac = $log['accion'] ?? '';
                                    $acClass = 'secondary';
                                    if (in_array($ac, ['crear'])) $acClass = 'success';
                                    elseif (in_array($ac, ['eliminar'])) $acClass = 'danger';
                                    elseif (in_array($ac, ['actualizar','restablecer'])) $acClass = 'warning';
                                    elseif (in_array($ac, ['login','logout'])) $acClass = 'info';
                                    ?>
                                    <span class="badge bg-<?php echo $acClass; ?>"><?php echo htmlspecialchars($ac, ENT_QUOTES, 'UTF-8'); ?></span>
                                </td>
                                <td><small><?php echo htmlspecialchars($log['tabla'] ?? '', ENT_QUOTES, 'UTF-8'); ?></small></td>
                                <td><small class="text-muted"><?php echo htmlspecialchars(mb_strimwidth($log['detalles'] ?? '', 0, 60, '...'), ENT_QUOTES, 'UTF-8'); ?></small></td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
                <?php else: ?>
                <div class="text-center py-5 text-muted">
                    <i class="bi bi-journal-x fs-1 d-block mb-2"></i>
                    No hay registros de auditor&iacute;a disponibles.
                </div>
                <?php endif; ?>
            </div>

            <!-- TAB 9: ERRORES -->
            <div class="tab-pane fade" id="tab-errores" role="tabpanel">
                <div class="tab-pane-header">
                    <h4><i class="bi bi-exclamation-octagon me-2 text-primary"></i>Registro de Errores</h4>
                    <p>Monitor de errores del sistema en tiempo real.</p>
                </div>
                <div class="text-center py-4">
                    <i class="bi bi-bug fs-1 text-danger d-block mb-3"></i>
                    <p class="text-muted mb-3">Accede al monitor completo de errores del sistema para diagn&oacute;stico y depuraci&oacute;n.</p>
                    <a href="<?php echo BASE_URL; ?>configuraciones/errores" class="btn btn-danger">
                        <i class="bi bi-exclamation-octagon me-2"></i>Ver Registro de Errores
                    </a>
                </div>
            </div>

            <!-- TAB 10: CHATBOT -->
            <div class="tab-pane fade" id="tab-chatbot" role="tabpanel">
                <div class="tab-pane-header">
                    <h4><i class="bi bi-whatsapp me-2 text-primary"></i>Configuraci&oacute;n del Chatbot de WhatsApp</h4>
                    <p>Configura el chatbot de WhatsApp para automatizar respuestas y notificaciones.</p>
                </div>
                <div class="row g-3 mb-3">
                    <div class="col-md-4">
                        <label class="form-label">Proveedor</label>
                        <select class="form-select" name="whatsapp_provider">
                            <?php $waProv = $config_all['whatsapp_provider'] ?? 'meta'; ?>
                            <option value="meta"      <?php echo $waProv == 'meta'      ? 'selected' : ''; ?>>Meta (WhatsApp Business API)</option>
                            <option value="twilio"    <?php echo $waProv == 'twilio'    ? 'selected' : ''; ?>>Twilio</option>
                            <option value="360dialog" <?php echo $waProv == '360dialog' ? 'selected' : ''; ?>>360dialog</option>
                            <option value="custom"    <?php echo $waProv == 'custom'    ? 'selected' : ''; ?>>Personalizado</option>
                        </select>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">N&uacute;mero de Tel&eacute;fono (E.164)</label>
                        <input type="text" class="form-control" name="whatsapp_phone_number"
                               value="<?php echo cfgVal($config_all, 'whatsapp_phone_number'); ?>"
                               placeholder="+521234567890">
                        <div class="form-text">N&uacute;mero de WhatsApp Business registrado.</div>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">Phone Number ID <small class="text-muted">(Meta)</small></label>
                        <input type="text" class="form-control font-monospace" name="whatsapp_phone_number_id"
                               value="<?php echo cfgVal($config_all, 'whatsapp_phone_number_id'); ?>"
                               placeholder="1234567890">
                        <div class="form-text">ID del n&uacute;mero en Meta Developers.</div>
                    </div>
                </div>
                <p class="section-subtitle"><i class="bi bi-key me-1"></i>Credenciales de API</p>
                <div class="row g-3">
                    <div class="col-12">
                        <label class="form-label">Access Token</label>
                        <input type="password" class="form-control font-monospace" name="whatsapp_access_token"
                               value="<?php echo cfgVal($config_all, 'whatsapp_access_token'); ?>"
                               placeholder="EAAG...">
                        <div class="form-text">Token de acceso permanente generado en Meta Developers.</div>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">API Key adicional</label>
                        <input type="password" class="form-control font-monospace" name="whatsapp_api_key"
                               value="<?php echo cfgVal($config_all, 'whatsapp_api_key'); ?>"
                               placeholder="sk-xxx...">
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Webhook Verify Token</label>
                        <input type="text" class="form-control font-monospace" name="whatsapp_webhook_verify_token"
                               value="<?php echo cfgVal($config_all, 'whatsapp_webhook_verify_token'); ?>"
                               placeholder="mi_token_secreto_123">
                        <div class="form-text">Token para verificar el webhook de Meta.</div>
                    </div>
                </div>
            </div>

        </div><!-- /.tab-content -->

        <!-- Save / Cancel (hidden for view-only tabs) -->
        <div class="d-flex justify-content-end gap-2 mt-4" id="saveButtons">
            <a href="<?php echo BASE_URL; ?>dashboard" class="btn btn-secondary">
                <i class="bi bi-x-circle me-1"></i>Cancelar
            </a>
            <button type="submit" class="btn btn-primary">
                <i class="bi bi-save me-1"></i>Guardar Cambios
            </button>
        </div>
        </form>
    </div><!-- /.col-lg-9 -->
</div><!-- /.row -->

<script>
(function () {
    'use strict';
    var VIEW_ONLY = ['#tab-bitacora', '#tab-errores'];
    var STORAGE_KEY = 'cfg_active_tab';

    function updateSaveButtons(tabHref) {
        var el = document.getElementById('saveButtons');
        if (el) { el.style.display = VIEW_ONLY.indexOf(tabHref) !== -1 ? 'none' : ''; }
    }

    // Restore last active tab
    var saved = sessionStorage.getItem(STORAGE_KEY);
    if (saved) {
        var el = document.querySelector('[href="' + saved + '"]');
        if (el) { bootstrap.Tab.getOrCreateInstance(el).show(); }
        updateSaveButtons(saved);
    }

    document.querySelectorAll('#settingsTabs .nav-link').forEach(function (el) {
        el.addEventListener('shown.bs.tab', function (e) {
            var href = e.target.getAttribute('href');
            sessionStorage.setItem(STORAGE_KEY, href);
            updateSaveButtons(href);
        });
    });

    // Color pickers
    function syncColor(pickerId, textId) {
        var picker = document.getElementById(pickerId);
        var text   = document.getElementById(textId);
        if (!picker || !text) return;
        picker.addEventListener('input', function () {
            text.value = this.value;
            updateColorPreview();
        });
        text.addEventListener('input', function () {
            if (/^#([0-9a-fA-F]{3}|[0-9a-fA-F]{6})$/.test(this.value)) {
                picker.value = this.value;
                updateColorPreview();
            }
        });
    }
    function updateColorPreview() {
        var p = document.getElementById('colorPrimario');
        var s = document.getElementById('colorSecundario');
        var preview = document.getElementById('colorPreview');
        if (p && s && preview) {
            preview.style.background = 'linear-gradient(135deg,' + p.value + ' 0%,' + s.value + ' 100%)';
        }
    }
    syncColor('colorPrimario', 'colorPrimarioText');
    syncColor('colorSecundario', 'colorSecundarioText');

    // Logo preview
    var logoInput = document.getElementById('logoInput');
    if (logoInput) {
        logoInput.addEventListener('change', function (e) {
            var file = e.target.files[0];
            if (!file || !file.type.startsWith('image/')) return;
            var reader = new FileReader();
            reader.onload = function (e) {
                var img = document.querySelector('#tab-sitio .logo-preview-new');
                if (!img) {
                    img = document.createElement('img');
                    img.className = 'logo-preview-new d-block mb-2';
                    img.style.cssText = 'max-height:60px;max-width:160px;object-fit:contain;border:1px solid #dee2e6;border-radius:6px;padding:5px;';
                    logoInput.parentElement.insertBefore(img, logoInput);
                }
                img.src = e.target.result;
            };
            reader.readAsDataURL(file);
        });
    }

    // Test email
    var btnTest = document.getElementById('btnTestEmail');
    if (btnTest) {
        btnTest.addEventListener('click', function () {
            var addr = document.getElementById('test_email_address').value.trim();
            var out  = document.getElementById('email_test_result');
            var btn  = this;
            if (!addr || !/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(addr)) {
                out.innerHTML = '<div class="alert alert-warning py-1 px-3 mt-1 small">Por favor ingresa un email v&aacute;lido.</div>';
                return;
            }
            btn.disabled = true;
            btn.innerHTML = '<span class="spinner-border spinner-border-sm me-1"></span>Enviando&hellip;';
            out.innerHTML = '<div class="alert alert-info py-1 px-3 mt-1 small">Enviando correo de prueba&hellip;</div>';
            fetch('<?php echo BASE_URL; ?>configuraciones/testEmail', {
                method: 'POST',
                headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                body: 'test_email=' + encodeURIComponent(addr)
            })
            .then(function (r) { return r.json(); })
            .then(function (d) {
                out.innerHTML = d.success
                    ? '<div class="alert alert-success py-1 px-3 mt-1 small"><i class="bi bi-check-circle me-1"></i>' + d.message + '</div>'
                    : '<div class="alert alert-danger py-1 px-3 mt-1 small"><i class="bi bi-x-circle me-1"></i>' + d.message + '</div>';
            })
            .catch(function (e) {
                out.innerHTML = '<div class="alert alert-danger py-1 px-3 mt-1 small">Error: ' + e.message + '</div>';
            })
            .finally(function () {
                btn.disabled = false;
                btn.innerHTML = '<i class="bi bi-send me-1"></i>Enviar prueba';
            });
        });
    }
})();
</script>
