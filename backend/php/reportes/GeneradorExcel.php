<?php
require $_SERVER['DOCUMENT_ROOT'] . '/sisti/vendor/autoload.php';

use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

class GeneradorExcel
{
    private $spreadsheet;
    private $hojas = [];

    public function __construct()
    {
        $this->spreadsheet = new Spreadsheet();
        $this->spreadsheet->removeSheetByIndex(0); // Eliminar hoja por defecto
    }

    public function agregarHoja($titulo)
    {
        $hoja = new \PhpOffice\PhpSpreadsheet\Worksheet\Worksheet($this->spreadsheet, $titulo);
        $this->spreadsheet->addSheet($hoja);
        $index = $this->spreadsheet->getIndex($hoja);
        $this->spreadsheet->setActiveSheetIndex($index);
        $this->hojas[$titulo] = $hoja;
        return $titulo;
    }

    public function escribirFilaEncabezado($tituloHoja, $encabezados)
    {
        $hoja = $this->hojas[$tituloHoja];
        $filaInicial = 4;
        $columnaInicial = 2; // Empezamos en columna C (ASCII C=67, 65+A=columna0)

        // Columna B = N°
        $hoja->setCellValue('B' . $filaInicial, 'N°');
        $hoja->getStyle('B' . $filaInicial)->getFont()->setBold(true);
        $hoja->getColumnDimension('B')->setAutoSize(true);
        $hoja->getStyle('B' . $filaInicial)->getFill()->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)
            ->getStartColor()->setARGB('FF2563EB');
        $hoja->getStyle('B' . $filaInicial)->getFont()->getColor()->setARGB('FFFFFFFF');
        $hoja->getStyle('B' . $filaInicial)->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);

        // Escribir encabezados a partir de C4
        foreach ($encabezados as $col => $valor) {
            $colLetra = chr(65 + $columnaInicial + $col); // C=67 ASCII
            $celda = $colLetra . $filaInicial;
            $hoja->setCellValue($celda, $valor);
            $hoja->getStyle($celda)->getFont()->setBold(true);
            $hoja->getColumnDimension($colLetra)->setAutoSize(true);
            $hoja->getStyle($celda)->getFill()->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)
                ->getStartColor()->setARGB('FF2563EB');
            $hoja->getStyle($celda)->getFont()->getColor()->setARGB('FFFFFFFF');
            $hoja->getStyle($celda)->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);
        }
    }

    public function escribirFilaDatos($tituloHoja, $datos)
    {
        $hoja = $this->hojas[$tituloHoja];
        $columnaInicial = 2; // Datos empiezan en columna C
        $filaInicio = $hoja->getHighestRow() + 1;
        if ($filaInicio < 5) $filaInicio = 5;

        // Número fila para N°
        $numeroFila = $filaInicio - 4; // Porque encabezado está en fila 4

        // Escribir número en columna B
        $hoja->setCellValue('B' . $filaInicio, $numeroFila);
        $hoja->getStyle('B' . $filaInicio)->getAlignment()
            ->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);

        foreach ($datos as $col => $valor) {
            $colIndex = $columnaInicial + $col;
            $colLetra = chr(65 + $colIndex);
            $celda = $colLetra . $filaInicio;

            // Solo formatear fechas en columnas específicas:
            // Col C=2: numero_ticket (NO fecha)
            // Col D=3: dni (NO fecha)
            // Col I=8: estado_texto (NO fecha)
            // Col J=9: fecha_creacion (SÍ fecha)
            // Col K=10: fecha_resuelto (SÍ fecha o guion)

            if (in_array($colIndex, [9, 10]) && $this->esFecha($valor)) {
                try {
                    $fechaObj = new DateTime($valor);
                    $valor = $fechaObj->format('d/m/Y H:i:s');
                } catch (Exception $e) {
                    // Si falla, dejar valor original
                }
            }

            $hoja->setCellValue($celda, $valor);

            // Centrar columnas específicas: ticket (C=2), dni (D=3), estado (I=8), fecha creación (J=9), fecha resuelto (K=10)
            if (in_array($colIndex, [2, 3, 8, 9, 10])) {
                $hoja->getStyle($celda)->getAlignment()
                    ->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);
            }
        }
    }

    private function esFecha($valor)
    {
        // Detectar si valor parece una fecha
        if (!$valor) return false;
        if (preg_match('/^\d{4}-\d{2}-\d{2}/', $valor)) return true; // YYYY-MM-DD ...
        if (strtotime($valor) !== false) return true;
        return false;
    }

    public function generar($nombreArchivo)
    {
        // Limpiar salida previa
        if (ob_get_length()) ob_end_clean();

        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        header("Content-Disposition: attachment;filename=\"$nombreArchivo.xlsx\"");
        header('Cache-Control: max-age=0');

        $writer = new Xlsx($this->spreadsheet);
        $writer->save('php://output');
        exit;
    }

    public function getSpreadsheet()
    {
        return $this->spreadsheet;
    }
}
