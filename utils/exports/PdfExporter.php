<?php
/**
 * Clase Helper para exportar reportes a PDF
 */
require_once __DIR__ . '/../../vendor/autoload.php';

use TCPDF;

class PdfExporter {
    
    private $pdf;
    private $title;
    private $orientation;
    
    public function __construct($title = 'Reporte', $orientation = 'P') {
        $this->title = $title;
        $this->orientation = $orientation; // P = Portrait, L = Landscape
        
        // Crear nuevo documento PDF
        $this->pdf = new TCPDF($orientation, PDF_UNIT, PDF_PAGE_FORMAT, true, 'UTF-8', false);
        
        // Configurar información del documento
        $this->pdf->SetCreator('Sistema Albercas');
        $this->pdf->SetAuthor('Sistema Albercas');
        $this->pdf->SetTitle($title);
        
        // Configurar márgenes
        $this->pdf->SetMargins(15, 27, 15);
        $this->pdf->SetHeaderMargin(5);
        $this->pdf->SetFooterMargin(10);
        
        // Auto page break
        $this->pdf->SetAutoPageBreak(TRUE, 25);
        
        // Configurar fuente
        $this->pdf->SetFont('helvetica', '', 10);
    }
    
    /**
     * Establecer encabezado personalizado
     */
    public function setHeader($logoPath = null, $title = null, $subtitle = null) {
        $title = $title ?? $this->title;
        
        // Método de encabezado personalizado
        $this->pdf->setHeaderCallback(function($pdf) use ($logoPath, $title, $subtitle) {
            if ($logoPath && file_exists($logoPath)) {
                $pdf->Image($logoPath, 15, 10, 30, '', '', '', 'T', false, 300, '', false, false, 0, false, false, false);
            }
            
            $pdf->SetFont('helvetica', 'B', 16);
            $pdf->Cell(0, 15, $title, 0, false, 'C', 0, '', 0, false, 'M', 'M');
            
            if ($subtitle) {
                $pdf->Ln();
                $pdf->SetFont('helvetica', '', 10);
                $pdf->Cell(0, 5, $subtitle, 0, false, 'C', 0, '', 0, false, 'M', 'M');
            }
        });
    }
    
    /**
     * Establecer pie de página personalizado
     */
    public function setFooter() {
        $this->pdf->setFooterCallback(function($pdf) {
            $pdf->SetY(-15);
            $pdf->SetFont('helvetica', 'I', 8);
            $pdf->Cell(0, 10, 'Página ' . $pdf->getAliasNumPage() . ' de ' . $pdf->getAliasNbPages(), 0, false, 'C', 0, '', 0, false, 'T', 'M');
        });
    }
    
    /**
     * Agregar nueva página
     */
    public function addPage() {
        $this->pdf->AddPage();
    }
    
    /**
     * Escribir texto HTML
     */
    public function writeHTML($html) {
        $this->pdf->writeHTML($html, true, false, true, false, '');
    }
    
    /**
     * Escribir celda
     */
    public function cell($width, $height, $text, $border = 0, $ln = 0, $align = 'L', $fill = false) {
        $this->pdf->Cell($width, $height, $text, $border, $ln, $align, $fill);
    }
    
    /**
     * Salto de línea
     */
    public function ln($h = '') {
        $this->pdf->Ln($h);
    }
    
    /**
     * Establecer color de relleno
     */
    public function setFillColor($r, $g, $b) {
        $this->pdf->SetFillColor($r, $g, $b);
    }
    
    /**
     * Establecer color de texto
     */
    public function setTextColor($r, $g, $b) {
        $this->pdf->SetTextColor($r, $g, $b);
    }
    
    /**
     * Establecer fuente
     */
    public function setFont($family, $style = '', $size = 0) {
        $this->pdf->SetFont($family, $style, $size);
    }
    
    /**
     * Crear tabla simple
     */
    public function createTable($headers, $data, $widths = null) {
        // Si no se especifican anchos, distribuir uniformemente
        if (!$widths) {
            $tableWidth = $this->pdf->getPageWidth() - 30; // Menos márgenes
            $colWidth = $tableWidth / count($headers);
            $widths = array_fill(0, count($headers), $colWidth);
        }
        
        // Encabezados
        $this->setFillColor(102, 126, 234);
        $this->setTextColor(255, 255, 255);
        $this->setFont('helvetica', 'B', 10);
        
        foreach ($headers as $i => $header) {
            $this->cell($widths[$i], 7, $header, 1, 0, 'C', true);
        }
        $this->ln();
        
        // Datos
        $this->setFillColor(245, 245, 245);
        $this->setTextColor(0, 0, 0);
        $this->setFont('helvetica', '', 9);
        
        $fill = false;
        foreach ($data as $row) {
            foreach ($row as $i => $cell) {
                $this->cell($widths[$i], 6, $cell, 1, 0, 'L', $fill);
            }
            $this->ln();
            $fill = !$fill;
        }
    }
    
    /**
     * Descargar el PDF
     */
    public function download($filename = 'reporte.pdf') {
        $this->pdf->Output($filename, 'D');
    }
    
    /**
     * Mostrar el PDF en el navegador
     */
    public function display($filename = 'reporte.pdf') {
        $this->pdf->Output($filename, 'I');
    }
    
    /**
     * Guardar el PDF en archivo
     */
    public function save($filepath) {
        $this->pdf->Output($filepath, 'F');
    }
    
    /**
     * Obtener el PDF como string
     */
    public function getString() {
        return $this->pdf->Output('', 'S');
    }
}
