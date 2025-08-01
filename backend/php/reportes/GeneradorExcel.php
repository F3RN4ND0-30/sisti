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

    public function escribirFilaEncabezado($tituloHoja, $datos)
    {
        $hoja = $this->hojas[$tituloHoja];
        $filaInicial = 4;
        $columnaInicial = 2; // C = índice 2, los encabezados empiezan en C

        // Columna B = N°
        $hoja->setCellValue('B' . $filaInicial, 'N°');
        $hoja->getStyle('B' . $filaInicial)->getFont()->setBold(true);
        $hoja->getColumnDimension('B')->setAutoSize(true);
        $hoja->getStyle('B' . $filaInicial)->getFill()->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)
            ->getStartColor()->setARGB('FF2563EB');
        $hoja->getStyle('B' . $filaInicial)->getFont()->getColor()->setARGB('FFFFFFFF');
        $hoja->getStyle('B' . $filaInicial)->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);

        // Ahora escribir los demás encabezados desde la columna C
        foreach ($datos as $col => $valor) {
            $colLetra = chr(65 + $columnaInicial + $col); // empezamos en C
            $celda = $colLetra . $filaInicial;
            $hoja->setCellValue($celda, $valor);
            $hoja->getStyle($celda)->getFont()->setBold(true);
            $hoja->getColumnDimension($colLetra)->setAutoSize(true);
            $hoja->getStyle($celda)->getFill()->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)
                ->getStartColor()->setARGB('FF2563EB');
            $hoja->getStyle($celda)->getFont()->getColor()->setARGB('FFFFFFFF');
        }

        // Centrar toda la fila 4 (encabezados) desde B4 hasta la última columna con datos
        $ultimaColLetra = chr(65 + $columnaInicial + count($datos) - 1);
        $rango = "B{$filaInicial}:{$ultimaColLetra}{$filaInicial}";
        $hoja->getStyle($rango)->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);
    }

    public function escribirFilaDatos($tituloHoja, $datos)
    {
        $hoja = $this->hojas[$tituloHoja];
        $columnaInicial = 2; // Datos empiezan en C
        $filaInicio = $hoja->getHighestRow() + 1;
        if ($filaInicio < 5) $filaInicio = 5;

        // Número de fila para la numeración
        $numeroFila = $filaInicio - 4; // fila 5 es el primer dato

        // Escribir número en columna B
        $hoja->setCellValue('B' . $filaInicio, $numeroFila);
        $hoja->getStyle('B' . $filaInicio)->getAlignment()
            ->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);

        foreach ($datos as $col => $valor) {
            $colIndex = $columnaInicial + $col;
            $colLetra = chr(65 + $colIndex);
            $celda = $colLetra . $filaInicio;

            // Formatear fecha (columna I, que ahora estará en la posición 9 porque desplazamos todo 1 columna)
            // Antes I era índice 8; ahora sería 9 (A=0, B=1, C=2 ... I=9)
            if ($colLetra === 'J') { // porque A=0, B=1, C=2 ... J=9 (la 9na columna después de desplazar)
                try {
                    $fechaObj = new DateTime($valor);
                    $valor = $fechaObj->format('d/m/Y H:i:s');
                } catch (Exception $e) {
                    // dejar valor original si falla
                }
            }

            $hoja->setCellValue($celda, $valor);

            if (in_array($colIndex, [2, 3, 8, 9])) {
                $hoja->getStyle($celda)->getAlignment()
                    ->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);
            }
        }
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

    // 🔹 Nuevo: permite obtener el objeto Spreadsheet para manipular desde fuera (ej. para título)
    public function getSpreadsheet()
    {
        return $this->spreadsheet;
    }
}
