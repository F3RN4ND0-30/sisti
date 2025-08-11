<?php
session_name('HELPDESK_SISTEMA');
session_start();

require_once $_SERVER['DOCUMENT_ROOT'] . '/sisti/vendor/autoload.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/sisti/backend/bd/conexion.php';

use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\Style\Alignment;

// Recoge los datos del formulario
$tipoFicha = $_POST['tipo'] ?? 'mantenimiento';
$unidadOrganica = $_POST['unidad_organica'] ?? '';
$trabajador = $_POST['trabajador_municipal'] ?? '';
$cargo = $_POST['cargo'] ?? '';
$dni = $_POST['dni_trabajador'] ?? '';
$requerimiento = $_POST['doc_requerimiento'] ?? '';
$tipo = $_POST['tipo'] ?? '';
$subtipo = $_POST['subtipo'] ?? '';
$descripcion = $_POST['descripcion'] ?? '';
$observacion = $_POST['observacion'] ?? '';
$tecnico = $_SESSION['hd_ficha'] ?? 'Técnico no identificado';
$idUsuario = $_SESSION['hd_id'] ?? null;
$fechaHoy = date('d-m-Y');

// Validación mínima
if (!$idUsuario) {
  die("Usuario no identificado.");
}

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
    'Sistema Vía Web' => ['check' => 'H41', 'descripcion' => 'I41'],
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

// Obtener nuevo número de ficha
$stmt = $conexion->query("SELECT MAX(Numero) AS ultimo FROM ficha_control");
$ultimoNumero = $stmt->fetchColumn();
$nuevoNumero = $ultimoNumero ? $ultimoNumero + 1 : 1;
$numeroFormateado = str_pad($nuevoNumero, 6, '0', STR_PAD_LEFT); // Ej: 000001

// Ruta a la plantilla
$plantillaPath = $_SERVER['DOCUMENT_ROOT'] . '/sisti/backend/php/excel/plantillas/ficha_mantenimiento.xlsx';
$spreadsheet = IOFactory::load($plantillaPath);
$sheet = $spreadsheet->getActiveSheet();

// Agregar número de ficha en celda J3 y centrar
$sheet->setCellValue('J3', 'N° ' . $numeroFormateado);
$sheet->getStyle('J3')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);

// Rellenar los datos
$sheet->setCellValue('C8', $unidadOrganica);
$sheet->setCellValue('J8', $fechaHoy);
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
$maxCharsPerRow = 108;
$startRow = 55;
$columnStart = 'C';
$columnEnd = 'L';

$lines = str_split($observacion, $maxCharsPerRow);

foreach ($lines as $i => $linea) {
  $fila = $startRow + $i;
  $celdaInicio = $columnStart . $fila;
  $celdaFin = $columnEnd . $fila;

  $sheet->mergeCells("$celdaInicio:$celdaFin");
  $sheet->setCellValue($celdaInicio, $linea);
  $sheet->getStyle("$celdaInicio:$celdaFin")->getAlignment()->setWrapText(true);
  $sheet->getRowDimension($fila)->setRowHeight(-1);
}

// Proteger la hoja
$sheet->getProtection()->setSheet(true);
$sheet->getProtection()->setSort(true);
$sheet->getProtection()->setInsertRows(true);
$sheet->getProtection()->setFormatCells(false);
$sheet->getProtection()->setPassword('CuandoPagan');

// Guardar archivo en carpeta uploads (crea la carpeta si no existe)
$uploadDir = $_SERVER['DOCUMENT_ROOT'] . '/sisti/archivos/fichas/';
if (!is_dir($uploadDir)) {
  mkdir($uploadDir, 0777, true);
}

$filename = "ficha_Mantenimiento_" . $numeroFormateado . ".xlsx";
$filePath = $uploadDir . $filename;

$writer = new Xlsx($spreadsheet);
$writer->save($filePath);

// Guardar ruta en la base de datos (solo la ruta relativa)
$rutaRelativa = "/sisti/archivos/fichas/" . $filename;

$sqlInsert = "INSERT INTO ficha_control (Numero, Id_Usuarios, ArchivoExcel)
              VALUES (:numero, :id_usuario, :rutaArchivo)";
$stmt = $conexion->prepare($sqlInsert);
$stmt->bindParam(':numero', $nuevoNumero, PDO::PARAM_INT);
$stmt->bindParam(':id_usuario', $idUsuario, PDO::PARAM_INT);
$stmt->bindParam(':rutaArchivo', $rutaRelativa, PDO::PARAM_STR);
$stmt->execute();

// Descargar el archivo directamente al navegador
header("Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet");
header("Content-Disposition: attachment; filename=\"$filename\"");
header("Cache-Control: max-age=0");

// Leer archivo y enviarlo
readfile($filePath);
exit;
