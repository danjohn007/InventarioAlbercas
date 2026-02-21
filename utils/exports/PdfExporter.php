<?php
/**
 * Clase Helper para exportar reportes a PDF
 *
 * Implementación pura en PHP: genera una página HTML optimizada para
 * impresión y la muestra directamente en el navegador, donde el usuario
 * puede usar "Imprimir → Guardar como PDF".
 * No requiere Composer ni ninguna librería externa.
 */
class PdfExporter {

    private $title;
    private $orientation;
    private $headerTitle  = '';
    private $headerSubtitle = '';

    /** @var string Contenido HTML acumulado */
    private $html = '';

    /** @var array Celdas pendientes de la fila actual (para cell() con ln=0) */
    private $pendingCells = [];

    private $fontBold = false;
    private $fontSize  = 10;

    public function __construct($title = 'Reporte', $orientation = 'P') {
        $this->title       = $title;
        $this->headerTitle = $title;
        $this->orientation = $orientation; // P = Portrait, L = Landscape
    }

    // ------------------------------------------------------------------ //
    //  API pública — idéntica a la versión original con TCPDF             //
    // ------------------------------------------------------------------ //

    public function setHeader($logoPath = null, $title = null, $subtitle = null) {
        if ($title)    $this->headerTitle    = $title;
        if ($subtitle) $this->headerSubtitle = $subtitle;
    }

    public function setFooter() { /* no-op; el pie se maneja con CSS @page */ }
    public function addPage()   { /* no-op; página única HTML               */ }

    public function setFillColor($r, $g, $b) { /* contexto manejado internamente */ }
    public function setTextColor($r, $g, $b) { /* contexto manejado internamente */ }

    public function setFont($family, $style = '', $size = 0) {
        $this->fontBold = (strpos(strtoupper((string)$style), 'B') !== false);
        if ($size > 0) {
            // Clamp to a safe CSS range to prevent layout issues
            $this->fontSize = max(6, min(72, (int)$size));
        }
    }

    /**
     * Añade una "celda" al contenido del reporte.
     *
     * @param int|float $width   Ancho (0 = ancho completo → encabezado de sección)
     * @param int|float $height  Altura (ignorada en HTML)
     * @param string    $text    Contenido
     * @param int       $border  Borde (0 = sin borde)
     * @param int       $ln      Salto de línea después (0 = continuar en la misma fila, 1 = nueva fila)
     * @param string    $align   Alineación: L | C | R
     * @param bool      $fill    Relleno de fondo claro
     */
    public function cell($width, $height, $text, $border = 0, $ln = 0, $align = 'L', $fill = false) {
        if ($width == 0) {
            // Celda de ancho completo → encabezado de sección
            $this->flushPendingCells();
            $boldStyle = $this->fontBold ? 'font-weight:bold;' : '';
            $sizeStyle = $this->fontSize >= 11 ? 'font-size:' . $this->fontSize . 'pt;' : '';
            $this->html .= '<p style="' . $boldStyle . $sizeStyle . 'margin:6px 0 2px;">'
                         . htmlspecialchars((string)$text) . "</p>\n";
        } else {
            $this->pendingCells[] = [
                'text'   => $text,
                'width'  => $width,
                'border' => $border,
                'align'  => $align,
                'fill'   => $fill,
                'bold'   => $this->fontBold,
                'size'   => $this->fontSize,
            ];
        }

        if ($ln) {
            $this->flushPendingCells();
        }
    }

    public function ln($h = '') {
        $this->flushPendingCells();
        $this->html .= "<div style=\"height:6px;\"></div>\n";
    }

    public function createTable($headers, $data, $widths = null) {
        $this->flushPendingCells();

        $this->html .= '<table style="width:100%;border-collapse:collapse;margin:8px 0;font-size:9pt;">';

        // Fila de encabezados
        $this->html .= '<thead><tr>';
        foreach ($headers as $i => $header) {
            $wStyle = ($widths && isset($widths[$i])) ? 'width:' . $widths[$i] . 'mm;' : '';
            $this->html .= '<th style="' . $wStyle
                         . 'background:#667eea;color:#fff;padding:4px 6px;'
                         . 'border:1px solid #4a5fc4;text-align:center;">'
                         . htmlspecialchars((string)$header) . '</th>';
        }
        $this->html .= '</tr></thead><tbody>';

        // Filas de datos
        foreach ($data as $ri => $row) {
            $bg = ($ri % 2 === 0) ? '#fff' : '#f7f7fb';
            $this->html .= '<tr style="background:' . $bg . ';">';
            foreach ($row as $cell) {
                $this->html .= '<td style="padding:3px 6px;border:1px solid #ddd;">'
                             . htmlspecialchars((string)$cell) . '</td>';
            }
            $this->html .= '</tr>';
        }

        $this->html .= '</tbody></table>';
    }

    /**
     * Envía la página HTML al navegador y abre el diálogo de impresión.
     * El usuario puede elegir "Guardar como PDF" en cualquier navegador moderno.
     */
    public function download($filename = 'reporte.pdf') {
        $this->flushPendingCells();

        $pageRule  = ($this->orientation === 'L')
            ? '@page{size:A4 landscape;margin:10mm;}'
            : '@page{size:A4 portrait;margin:15mm;}';

        $pageTitle = htmlspecialchars($this->headerTitle ?: $this->title);
        $subtitle  = htmlspecialchars($this->headerSubtitle);

        header('Content-Type: text/html; charset=UTF-8');

        echo '<!DOCTYPE html><html lang="es"><head>';
        echo '<meta charset="UTF-8">';
        echo '<meta name="viewport" content="width=device-width,initial-scale=1">';
        echo '<title>' . $pageTitle . '</title>';
        echo '<style>';
        echo $pageRule;
        echo 'body{font-family:Arial,Helvetica,sans-serif;font-size:10pt;color:#222;margin:0;padding:10px;}';
        echo '.no-print{background:#eef2ff;padding:10px;text-align:center;margin-bottom:12px;border-radius:6px;border:1px solid #c7d2fe;}';
        echo '.no-print button{background:#667eea;color:#fff;border:none;padding:8px 22px;font-size:11pt;cursor:pointer;border-radius:4px;margin:0 6px;}';
        echo '.no-print button.sec{background:#6b7280;}';
        echo '.rpt-header{text-align:center;border-bottom:2px solid #667eea;margin-bottom:12px;padding-bottom:8px;}';
        echo '.rpt-header h1{font-size:15pt;margin:0 0 4px;color:#333;}';
        echo '.rpt-header p{font-size:9pt;color:#666;margin:0;}';
        echo 'table{width:100%;border-collapse:collapse;margin:8px 0;font-size:9pt;}';
        echo 'thead th{background:#667eea!important;color:#fff!important;-webkit-print-color-adjust:exact;print-color-adjust:exact;}';
        echo 'tr:nth-child(even) td{background:#f7f7fb!important;-webkit-print-color-adjust:exact;print-color-adjust:exact;}';
        echo '@media print{';
        echo '.no-print{display:none!important;}';
        echo 'body{padding:0;}';
        echo 'table{page-break-inside:auto;}';
        echo 'tr{page-break-inside:avoid;}';
        echo '}';
        echo '</style></head><body>';

        echo '<div class="no-print">';
        echo '<button onclick="window.print()">🖨&nbsp; Imprimir / Guardar PDF</button>';
        echo '<button class="sec" onclick="history.back()">&#8592;&nbsp; Volver al reporte</button>';
        echo '</div>';

        echo '<div class="rpt-header">';
        echo '<h1>' . $pageTitle . '</h1>';
        if ($subtitle) echo '<p>' . $subtitle . '</p>';
        echo '</div>';

        echo $this->html;

        // Disparar diálogo de impresión automáticamente con breve retardo
        // (300 ms) para que el contenido cargue y lectores de pantalla puedan
        // procesar la página antes de que se abra el diálogo.
        echo '<script>window.addEventListener("load",function(){setTimeout(function(){window.print();},300);});</script>';
        echo '</body></html>';
        exit;
    }

    // Métodos de compatibilidad adicionales
    public function writeHTML($html) { $this->html .= $html; }
    public function display($filename = 'reporte.pdf') { $this->download($filename); }
    public function save($filepath) { /* no-op */ }
    public function getString() { return $this->html; }

    // ------------------------------------------------------------------ //
    //  Internals                                                           //
    // ------------------------------------------------------------------ //

    private function flushPendingCells() {
        if (empty($this->pendingCells)) return;

        $alignMap = ['L' => 'left', 'C' => 'center', 'R' => 'right'];
        $this->html .= '<div style="display:flex;margin:1px 0;">';
        foreach ($this->pendingCells as $c) {
            $st  = 'flex:' . $c['width'] . ';padding:2px 5px;';
            $st .= 'text-align:' . ($alignMap[$c['align']] ?? 'left') . ';';
            $st .= 'font-size:' . ($c['size'] >= 9 ? $c['size'] : 9) . 'pt;';
            if ($c['bold'])   $st .= 'font-weight:bold;';
            if ($c['fill'])   $st .= 'background:#f0f0f0;';
            if ($c['border']) $st .= 'border:1px solid #aaa;';
            $this->html .= '<div style="' . $st . '">'
                         . htmlspecialchars((string)$c['text']) . '</div>';
        }
        $this->html .= "</div>\n";
        $this->pendingCells = [];
    }
}

