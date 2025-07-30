<?php
require_once $_SERVER['DOCUMENT_ROOT'] . '/HelpDesk_MPP2.0/vendor/autoload.php';

use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

// Recoge los datos del formulario
$tipoFicha = $_POST['tipo'] ?? 'mantenimiento';
$unidadOrganica = $_POST['unidad_organica'] ?? '';
$fecha = $_POST['fecha'] ?? '';
$trabajador = $_POST['trabajador_municipal'] ?? '';
$cargo = $_POST['cargo'] ?? '';
$dni = $_POST['dni_trabajador'] ?? '';
$requerimiento = $_POST['doc_requerimiento'] ?? '';
$tecnico = $_POST['nombre_tecnico'] ?? '';
$tipo = $_POST['tipo'] ?? '';
$subtipo = $_POST['subtipo'] ?? '';
$descripcion = $_POST['descripcion'] ?? '';
$observacion = $_POST['observacion'] ?? '';

$ubicaciones = [
  'hardware' => [
    'CPU' => ['check' => 'C35', 'descripcion' => 'D35'],
    'Monitor' => ['check' => 'C36', 'descripcion' => 'D36'],
    'Teclado' => ['check' => 'C37', 'descripcion' => 'D37'],
    'Mouse' => ['check' => 'C38', 'descripcion' => 'D38'],
    'Estabilizador' => ['check' => 'C39', 'descripcion' => 'D39'],
    'Impresora' => ['check' => 'C40', 'descripcion' => 'D40'],
    'Supresor de Pico' => ['check' => 'C41', 'descripcion' => 'D41'],
    'Otros' => ['check' => 'C42', 'descripcion' => 'D42'],
  ],
  'redes' => [
    'Internet' => ['check' => 'H46', 'descripcion' => 'I46'],
    'Modem' => ['check' => 'H47', 'descripcion' => 'I47'],
    'Router' => ['check' => 'H48', 'descripcion' => 'I48'],
    'Switch' => ['check' => 'H49', 'descripcion' => 'I49'],
    'Cableado' => ['check' => 'H50', 'descripcion' => 'I50'],
    'Otros' => ['check' => 'H51', 'descripcion' => 'I51'],
  ],
  'sistemas' => [
    'SIAF' => ['check' => 'H35', 'descripcion' => 'I35'],
    'SIGA' => ['check' => 'H36', 'descripcion' => 'I36'],
    'Sistema Registro Civil' => ['check' => 'H37', 'descripcion' => 'I37'],
    'RUBEM' => ['check' => 'H38', 'descripcion' => 'I38'],
    'RUB PVL 20' => ['check' => 'H39', 'descripcion' => 'I39'],
    'SISPLA' => ['check' => 'H40', 'descripcion' => 'I40'],
    'Sistema Via Web' => ['check' => 'H41', 'descripcion' => 'I41'],
    'Otros' => ['check' => 'H42', 'descripcion' => 'I42'],
  ],
  'software' => [
    'Sistema Operativo' => ['check' => 'C46', 'descripcion' => 'D46'],
    'Word' => ['check' => 'C47', 'descripcion' => 'D47'],
    'Excel' => ['check' => 'C48', 'descripcion' => 'D48'],
    'Power Point' => ['check' => 'C49', 'descripcion' => 'D49'],
    'Internet' => ['check' => 'C50', 'descripcion' => 'D50'],
    'Antivirus' => ['check' => 'C51', 'descripcion' => 'D51'],
    'Otros' => ['check' => 'C52', 'descripcion' => 'D52'],
  ]
];

// Ruta a la plantilla
$plantillaPath = $_SERVER['DOCUMENT_ROOT'] . '/HelpDesk_MPP2.0/backend/php/excel/plantillas/ficha_mantenimiento.xlsx';
$spreadsheet = IOFactory::load($plantillaPath);
$sheet = $spreadsheet->getActiveSheet();

// Rellenar las celdas (ajusta las coordenadas según tu diseño)
$sheet->setCellValue('C8', $unidadOrganica);
$sheet->setCellValue('J8', $fecha);
$sheet->setCellValue('C11', $trabajador);
$sheet->setCellValue('C14', $cargo);
$sheet->setCellValue('J14', $dni);
$sheet->setCellValue('C22', $requerimiento);
$sheet->setCellValue('C24', $tecnico);

// Coloca "X" y la descripción según tipo y subtipo
if (isset($ubicaciones[$tipo][$subtipo])) {
  $celdaCheck = $ubicaciones[$tipo][$subtipo]['check'];
  $celdaDescripcion = $ubicaciones[$tipo][$subtipo]['descripcion'];

  $sheet->setCellValue($celdaCheck, 'X');
  $sheet->setCellValue($celdaDescripcion, $descripcion);
}

// Observación
$maxCharsPerRow = 108; // Ajustado según tu texto de muestra
$startRow = 55;
$columnStart = 'C';
$columnEnd = 'L';

$lines = str_split($observacion, $maxCharsPerRow);

foreach ($lines as $i => $linea) {
  $fila = $startRow + $i;
  $celdaInicio = $columnStart . $fila;
  $celdaFin = $columnEnd . $fila;

  // Fusionar celdas y asignar valor
  $sheet->mergeCells("$celdaInicio:$celdaFin");
  $sheet->setCellValue($celdaInicio, $linea);

  // Ajustar alineación y permitir salto de línea
  $sheet->getStyle("$celdaInicio:$celdaFin")->getAlignment()->setWrapText(true);
  $sheet->getRowDimension($fila)->setRowHeight(-1); // Autoajuste de alto
}

// Descargar el archivo
$filename = "ficha_Mantenimiento" . date('Ymd_His') . ".xlsx";

header("Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet");
header("Content-Disposition: attachment; filename=\"$filename\"");
header("Cache-Control: max-age=0");

$writer = new Xlsx($spreadsheet);
$writer->save('php://output');
exit;
