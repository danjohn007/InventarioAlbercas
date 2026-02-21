<?php
/**
 * Clase Helper para exportar reportes a Excel
 *
 * Implementación pura en PHP usando el formato XML Spreadsheet 2003.
 * No requiere Composer ni ninguna librería externa.
 * Los archivos generados (.xls XML) son abiertos correctamente por
 * Microsoft Excel, LibreOffice Calc y Google Sheets.
 */
class ExcelExporter {

    private $title;
    private $sheetTitle;
    /** @var array[] Filas internas: cada elemento es un array de celdas */
    private $rows = [];

    public function __construct($title = 'Reporte') {
        $this->title      = $title;
        $this->sheetTitle = substr($title, 0, 31); // Excel limita a 31 chars
    }

    // ------------------------------------------------------------------ //
    //  API pública — idéntica a la versión original con PhpSpreadsheet    //
    // ------------------------------------------------------------------ //

    public function setReportTitle($title, $subtitle = null) {
        $this->addRow([['v' => $title,    's' => 'title',    'merge' => 10]]);
        if ($subtitle) {
            $this->addRow([['v' => $subtitle, 's' => 'subtitle', 'merge' => 10]]);
        }
        $this->addRow([['v' => 'Generado: ' . date('d/m/Y H:i'), 's' => 'subtitle', 'merge' => 10]]);
        $this->addRow([]); // espaciador
    }

    public function addSummary($sectionTitle, $data) {
        $this->addRow([['v' => $sectionTitle, 's' => 'section']]);
        foreach ($data as $label => $value) {
            $this->addRow([
                ['v' => $label, 's' => 'label'],
                ['v' => $value, 's' => 'normal'],
            ]);
        }
        $this->addRow([]); // espaciador
    }

    public function createTable($headers, $data, $startRow = null) {
        $headerRow = [];
        foreach ($headers as $h) {
            $headerRow[] = ['v' => $h, 's' => 'header'];
        }
        $this->addRow($headerRow);

        foreach ($data as $row) {
            $cells = [];
            foreach ($row as $cell) {
                $cells[] = ['v' => $cell, 's' => 'normal'];
            }
            $this->addRow($cells);
        }
        $this->addRow([]); // espaciador
    }

    public function download($filename = 'reporte.xlsx') {
        // Sanitize filename: strip path separators, control chars and header-injection chars
        $filename = preg_replace('/[\r\n\t"\'\\\\\/]/', '_', basename($filename));
        // El formato XML Spreadsheet 2003 usa extensión .xls
        $filename = preg_replace('/\.xlsx?$/i', '.xls', $filename);

        header('Content-Type: application/vnd.ms-excel; charset=UTF-8');
        header('Content-Disposition: attachment; filename="' . $filename . '"');
        header('Cache-Control: max-age=0');
        header('Pragma: cache');

        echo $this->buildXml();
        exit;
    }

    // Métodos de compatibilidad (no-op en esta implementación)
    public function writeCell($column, $row, $value, $bold = false) {}
    public function mergeCells($range) {}
    public function setColumnWidth($column, $width) {}
    public function getSheet() { return null; }
    public function getSpreadsheet() { return null; }
    public function save($filepath) { file_put_contents($filepath, $this->buildXml()); }

    // ------------------------------------------------------------------ //
    //  Internals                                                           //
    // ------------------------------------------------------------------ //

    private function addRow($cells) {
        $this->rows[] = $cells;
    }

    private function buildXml() {
        $xml  = "<?xml version=\"1.0\" encoding=\"UTF-8\"?>\n";
        $xml .= "<Workbook xmlns=\"urn:schemas-microsoft-com:office:spreadsheet\"\n";
        $xml .= " xmlns:ss=\"urn:schemas-microsoft-com:office:spreadsheet\">\n";

        // Estilos
        $xml .= "<Styles>\n";
        $xml .= '<Style ss:ID="title">'
              . '<Font ss:Bold="1" ss:Size="16" ss:Name="Arial"/>'
              . '<Alignment ss:Horizontal="Center"/>'
              . "</Style>\n";
        $xml .= '<Style ss:ID="subtitle">'
              . '<Font ss:Size="10" ss:Name="Arial" ss:Italic="1"/>'
              . '<Alignment ss:Horizontal="Center"/>'
              . "</Style>\n";
        $xml .= '<Style ss:ID="section">'
              . '<Font ss:Bold="1" ss:Size="12" ss:Name="Arial"/>'
              . "</Style>\n";
        $xml .= '<Style ss:ID="label">'
              . '<Font ss:Bold="1" ss:Name="Arial"/>'
              . '<Interior ss:Color="#EEEEEE" ss:Pattern="Solid"/>'
              . "</Style>\n";
        $xml .= '<Style ss:ID="header">'
              . '<Font ss:Bold="1" ss:Color="#FFFFFF" ss:Name="Arial"/>'
              . '<Interior ss:Color="#667EEA" ss:Pattern="Solid"/>'
              . '<Alignment ss:Horizontal="Center"/>'
              . "</Style>\n";
        $xml .= '<Style ss:ID="normal">'
              . '<Font ss:Name="Arial"/>'
              . '<Alignment ss:WrapText="1"/>'
              . "</Style>\n";
        $xml .= "</Styles>\n";

        // Hoja
        $xml .= '<Worksheet ss:Name="' . htmlspecialchars($this->sheetTitle, ENT_XML1) . '">' . "\n";
        $xml .= "<Table>\n";

        foreach ($this->rows as $rowCells) {
            $xml .= "<Row>\n";
            if (empty($rowCells)) {
                $xml .= "<Cell><Data ss:Type=\"String\"></Data></Cell>\n";
            } else {
                foreach ($rowCells as $cell) {
                    $style   = $cell['s'] ?? 'normal';
                    $merge   = isset($cell['merge'])
                             ? ' ss:MergeAcross="' . ((int)$cell['merge'] - 1) . '"'
                             : '';
                    $val     = (string)($cell['v'] ?? '');
                    $type    = (is_numeric($val) && $val !== '') ? 'Number' : 'String';
                    $escaped = htmlspecialchars($val, ENT_XML1 | ENT_QUOTES, 'UTF-8');
                    $xml .= '<Cell ss:StyleID="' . $style . '"' . $merge . '>'
                          . '<Data ss:Type="' . $type . '">' . $escaped . '</Data>'
                          . "</Cell>\n";
                }
            }
            $xml .= "</Row>\n";
        }

        $xml .= "</Table>\n";
        $xml .= "</Worksheet>\n";
        $xml .= "</Workbook>\n";

        return $xml;
    }
}

