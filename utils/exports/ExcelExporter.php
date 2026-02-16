<?php
/**
 * Clase Helper para exportar reportes a Excel
 */
require_once __DIR__ . '/../../vendor/autoload.php';

use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Fill;

class ExcelExporter {
    
    private $spreadsheet;
    private $sheet;
    private $title;
    private $currentRow = 1;
    
    public function __construct($title = 'Reporte') {
        $this->title = $title;
        $this->spreadsheet = new Spreadsheet();
        $this->sheet = $this->spreadsheet->getActiveSheet();
        $this->sheet->setTitle(substr($title, 0, 31)); // Excel limita a 31 caracteres
    }
    
    /**
     * Establecer título del reporte
     */
    public function setReportTitle($title, $subtitle = null) {
        // Título principal
        $this->sheet->setCellValue('A1', $title);
        $this->sheet->mergeCells('A1:F1');
        $this->sheet->getStyle('A1')->getFont()->setBold(true)->setSize(16);
        $this->sheet->getStyle('A1')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
        $this->currentRow = 2;
        
        // Subtítulo
        if ($subtitle) {
            $this->sheet->setCellValue('A2', $subtitle);
            $this->sheet->mergeCells('A2:F2');
            $this->sheet->getStyle('A2')->getFont()->setSize(10);
            $this->sheet->getStyle('A2')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
            $this->currentRow = 3;
        }
        
        // Fecha de generación
        $this->sheet->setCellValue('A' . $this->currentRow, 'Generado: ' . date('d/m/Y H:i'));
        $this->sheet->mergeCells('A' . $this->currentRow . ':F' . $this->currentRow);
        $this->sheet->getStyle('A' . $this->currentRow)->getFont()->setItalic(true)->setSize(9);
        $this->sheet->getStyle('A' . $this->currentRow)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
        
        $this->currentRow += 2; // Espacio
    }
    
    /**
     * Crear tabla con encabezados y datos
     */
    public function createTable($headers, $data, $startRow = null) {
        if ($startRow !== null) {
            $this->currentRow = $startRow;
        }
        
        $startRow = $this->currentRow;
        $col = 'A';
        
        // Encabezados
        foreach ($headers as $header) {
            $this->sheet->setCellValue($col . $this->currentRow, $header);
            $this->sheet->getStyle($col . $this->currentRow)->applyFromArray([
                'font' => ['bold' => true, 'color' => ['rgb' => 'FFFFFF']],
                'fill' => [
                    'fillType' => Fill::FILL_SOLID,
                    'startColor' => ['rgb' => '667eea']
                ],
                'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER]
            ]);
            $col++;
        }
        
        $this->currentRow++;
        
        // Datos
        foreach ($data as $row) {
            $col = 'A';
            foreach ($row as $cell) {
                $this->sheet->setCellValue($col . $this->currentRow, $cell);
                $col++;
            }
            $this->currentRow++;
        }
        
        // Aplicar bordes a toda la tabla
        $endCol = chr(ord('A') + count($headers) - 1);
        $endRow = $this->currentRow - 1;
        
        $this->sheet->getStyle('A' . $startRow . ':' . $endCol . $endRow)->applyFromArray([
            'borders' => [
                'allBorders' => [
                    'borderStyle' => Border::BORDER_THIN,
                    'color' => ['rgb' => '000000']
                ]
            ]
        ]);
        
        // Auto-ajustar columnas
        foreach (range('A', $endCol) as $col) {
            $this->sheet->getColumnDimension($col)->setAutoSize(true);
        }
        
        $this->currentRow++; // Espacio después de la tabla
    }
    
    /**
     * Agregar sección de resumen
     */
    public function addSummary($title, $data) {
        $this->currentRow++;
        
        // Título de la sección
        $this->sheet->setCellValue('A' . $this->currentRow, $title);
        $this->sheet->getStyle('A' . $this->currentRow)->getFont()->setBold(true)->setSize(12);
        $this->currentRow++;
        
        // Datos del resumen
        foreach ($data as $label => $value) {
            $this->sheet->setCellValue('A' . $this->currentRow, $label);
            $this->sheet->setCellValue('B' . $this->currentRow, $value);
            $this->sheet->getStyle('A' . $this->currentRow)->getFont()->setBold(true);
            $this->currentRow++;
        }
        
        $this->currentRow++; // Espacio
    }
    
    /**
     * Escribir celda en posición específica
     */
    public function writeCell($column, $row, $value, $bold = false) {
        $this->sheet->setCellValue($column . $row, $value);
        if ($bold) {
            $this->sheet->getStyle($column . $row)->getFont()->setBold(true);
        }
    }
    
    /**
     * Combinar celdas
     */
    public function mergeCells($range) {
        $this->sheet->mergeCells($range);
    }
    
    /**
     * Establecer ancho de columna
     */
    public function setColumnWidth($column, $width) {
        $this->sheet->getColumnDimension($column)->setWidth($width);
    }
    
    /**
     * Descargar el archivo Excel
     */
    public function download($filename = 'reporte.xlsx') {
        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        header('Content-Disposition: attachment;filename="' . $filename . '"');
        header('Cache-Control: max-age=0');
        
        $writer = new Xlsx($this->spreadsheet);
        $writer->save('php://output');
        exit;
    }
    
    /**
     * Guardar el archivo Excel
     */
    public function save($filepath) {
        $writer = new Xlsx($this->spreadsheet);
        $writer->save($filepath);
    }
    
    /**
     * Obtener la hoja de cálculo
     */
    public function getSheet() {
        return $this->sheet;
    }
    
    /**
     * Obtener el objeto Spreadsheet
     */
    public function getSpreadsheet() {
        return $this->spreadsheet;
    }
}
