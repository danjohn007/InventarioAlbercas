<?php
/**
 * Clase Helper para exportar reportes a Excel
 *
 * Implementación pura en PHP usando HTML con extensión .xls.
 * Esta es la técnica más compatible para exportar a Excel sin Composer:
 * - Microsoft Excel (Windows y macOS): abre sin advertencias
 * - LibreOffice Calc: abre sin advertencias
 * - Google Sheets: importable directamente
 * No requiere ninguna librería externa.
 */
class ExcelExporter {

    private $title;
    private $sheetTitle;

    /** @var array[] Filas internas con tipo y celdas */
    private $rows = [];

    /** Número máximo de columnas (para colspans correctos) */
    private $maxCols = 1;

    public function __construct($title = 'Reporte') {
        $this->title      = $title;
        $this->sheetTitle = substr($title, 0, 31);
    }

    // ------------------------------------------------------------------ //
    //  API pública — idéntica a la versión anterior                       //
    // ------------------------------------------------------------------ //

    public function setReportTitle($title, $subtitle = null) {
        $this->rows[] = ['type' => 'title',    'text' => $title];
        if ($subtitle) {
            $this->rows[] = ['type' => 'subtitle', 'text' => $subtitle];
        }
        $this->rows[] = ['type' => 'subtitle', 'text' => 'Generado: ' . date('d/m/Y H:i')];
        $this->rows[] = ['type' => 'spacer'];
    }

    public function addSummary($sectionTitle, $data) {
        $this->rows[] = ['type' => 'section', 'text' => $sectionTitle];
        foreach ($data as $label => $value) {
            $this->rows[] = ['type' => 'summary_row', 'label' => $label, 'value' => $value];
            $this->maxCols = max($this->maxCols, 2);
        }
        $this->rows[] = ['type' => 'spacer'];
    }

    public function createTable($headers, $data, $startRow = null) {
        $cols = count($headers);
        $this->maxCols = max($this->maxCols, $cols);
        $this->rows[] = ['type' => 'table_header', 'cells' => $headers];
        foreach ($data as $row) {
            $this->rows[] = ['type' => 'table_row', 'cells' => array_values((array)$row)];
        }
        $this->rows[] = ['type' => 'spacer'];
    }

    public function download($filename = 'reporte.xlsx') {
        // Sanitize filename
        $filename = preg_replace('/[\r\n\t"\'\\\\\/]/', '_', basename($filename));
        // Use .xls extension with HTML content — the most universally compatible
        // approach for PHP-without-Composer Excel exports.
        // Excel/LibreOffice/Sheets all open HTML+.xls without any warning.
        $filename = preg_replace('/\.xlsx?$|\.xml$/i', '.xls', $filename);

        header('Content-Type: application/vnd.ms-excel; charset=UTF-8');
        header('Content-Disposition: attachment; filename="' . $filename . '"');
        header('Cache-Control: max-age=0');
        header('Pragma: cache');

        echo $this->buildHtml();
        exit;
    }

    // Métodos de compatibilidad (no-op)
    public function writeCell($column, $row, $value, $bold = false) {}
    public function mergeCells($range) {}
    public function setColumnWidth($column, $width) {}
    public function getSheet() { return null; }
    public function getSpreadsheet() { return null; }
    public function save($filepath) { file_put_contents($filepath, $this->buildHtml()); }

    // ------------------------------------------------------------------ //
    //  Internals                                                           //
    // ------------------------------------------------------------------ //

    private function buildHtml() {
        $cols = max($this->maxCols, 1);

        $html  = '<html xmlns:o="urn:schemas-microsoft-com:office:office"';
        $html .= ' xmlns:x="urn:schemas-microsoft-com:office:excel"';
        $html .= ' xmlns="http://www.w3.org/TR/REC-html40">' . "\n";
        $html .= "<head>\n";
        $html .= '<meta http-equiv="Content-Type" content="text/html; charset=UTF-8">' . "\n";
        $html .= "<!--[if gte mso 9]><xml>\n";
        $html .= "  <x:ExcelWorkbook>\n";
        $html .= "    <x:ExcelWorksheets>\n";
        $html .= "      <x:ExcelWorksheet>\n";
        $html .= '        <x:Name>' . htmlspecialchars($this->sheetTitle) . "</x:Name>\n";
        $html .= "        <x:WorksheetOptions><x:DisplayGridlines/></x:WorksheetOptions>\n";
        $html .= "      </x:ExcelWorksheet>\n";
        $html .= "    </x:ExcelWorksheets>\n";
        $html .= "  </x:ExcelWorkbook>\n";
        $html .= "</xml><![endif]-->\n";
        $html .= "<style>\n";
        $html .= "body{font-family:Arial,sans-serif;font-size:10pt;}\n";
        $html .= ".title{font-size:16pt;font-weight:bold;text-align:center;}\n";
        $html .= ".subtitle{font-size:10pt;font-style:italic;text-align:center;color:#555;}\n";
        $html .= ".section{font-size:12pt;font-weight:bold;}\n";
        $html .= ".lbl{font-weight:bold;background:#EEEEEE;}\n";
        $html .= ".th{font-weight:bold;background:#667EEA;color:#FFFFFF;text-align:center;}\n";
        $html .= "td,th{border:1px solid #CCCCCC;padding:4px 8px;}\n";
        $html .= "tr.odd td{background:#F7F7FB;}\n";
        $html .= "</style>\n";
        $html .= "</head>\n<body>\n";
        $html .= '<table border="1" cellspacing="0" cellpadding="4">' . "\n";

        $oddRow = false;
        foreach ($this->rows as $row) {
            switch ($row['type']) {
                case 'title':
                    $html .= '<tr><td colspan="' . $cols . '" class="title">'
                           . htmlspecialchars((string)$row['text']) . "</td></tr>\n";
                    break;

                case 'subtitle':
                    $html .= '<tr><td colspan="' . $cols . '" class="subtitle">'
                           . htmlspecialchars((string)$row['text']) . "</td></tr>\n";
                    break;

                case 'section':
                    $html .= '<tr><td colspan="' . $cols . '" class="section">'
                           . htmlspecialchars((string)$row['text']) . "</td></tr>\n";
                    break;

                case 'summary_row':
                    if ($cols >= 2) {
                        $rest  = $cols - 2;
                        $extra = ($rest > 0) ? ' colspan="' . ($rest + 1) . '"' : '';
                        $html .= '<tr><td class="lbl">'
                               . htmlspecialchars((string)$row['label']) . '</td>'
                               . '<td' . $extra . '>'
                               . htmlspecialchars((string)$row['value']) . "</td></tr>\n";
                    } else {
                        $html .= '<tr><td class="lbl">'
                               . htmlspecialchars((string)$row['label'])
                               . ' ' . htmlspecialchars((string)$row['value'])
                               . "</td></tr>\n";
                    }
                    break;

                case 'table_header':
                    $html .= '<tr>';
                    foreach ($row['cells'] as $cell) {
                        $html .= '<th class="th">' . htmlspecialchars((string)$cell) . '</th>';
                    }
                    // Pad to maxCols if needed
                    for ($i = count($row['cells']); $i < $cols; $i++) {
                        $html .= '<th class="th"></th>';
                    }
                    $html .= "</tr>\n";
                    $oddRow = false;
                    break;

                case 'table_row':
                    $class = $oddRow ? ' class="odd"' : '';
                    $html .= '<tr' . $class . '>';
                    foreach ($row['cells'] as $cell) {
                        $html .= '<td>' . htmlspecialchars((string)$cell) . '</td>';
                    }
                    for ($i = count($row['cells']); $i < $cols; $i++) {
                        $html .= '<td></td>';
                    }
                    $html .= "</tr>\n";
                    $oddRow = !$oddRow;
                    break;

                case 'spacer':
                    $html .= '<tr><td colspan="' . $cols . '">&nbsp;</td></tr>' . "\n";
                    break;
            }
        }

        $html .= "</table>\n</body>\n</html>\n";
        return $html;
    }
}


