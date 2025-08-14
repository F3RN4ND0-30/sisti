<?php
session_name('HELPDESK_SISTEMA');
session_start();

require_once $_SERVER['DOCUMENT_ROOT'] . '/sisti/vendor/autoload.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/sisti/backend/bd/conexion.php'; // Tu conexión a BD

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
$idUsuario = $_SESSION['hd_id'] ?? null; // ID usuario para BD
$fechaHoy = date('d-m-Y');

if (!$idUsuario) {
  die("Usuario no identificado.");
}

$ubicaciones = [
  'hardware' => [
    'CPU' => ['check' => 'C35', 'descripcion' => 'D35'],
    'MONITOR' => ['check' => 'C36', 'descripcion' => 'D36'],
    'TECLADO' => ['check' => 'C37', 'descripcion' => 'D37'],
    'MOUSE' => ['check' => 'C38', 'descripcion' => 'D38'],
    'ESTABILIZADOR' => ['check' => 'C39', 'descripcion' => 'D39'],
    'IMPRESORA' => ['check' => 'C40', 'descripcion' => 'D40'],
    'SUPRESOR DE PICO' => ['check' => 'C41', 'descripcion' => 'D41'],
    'OTROS' => ['check' => 'C42', 'descripcion' => 'D42'],
  ],
  'redes' => [
    'INTERNET' => ['check' => 'H46', 'descripcion' => 'I46'],
    'MODEM' => ['check' => 'H47', 'descripcion' => 'I47'],
    'ROUTER' => ['check' => 'H48', 'descripcion' => 'I48'],
    'SWITCH' => ['check' => 'H49', 'descripcion' => 'I49'],
    'CABLEADO' => ['check' => 'H50', 'descripcion' => 'I50'],
    'OTROS' => ['check' => 'H51', 'descripcion' => 'I51'],
  ],
  'sistemas' => [
    'SIAF' => ['check' => 'H35', 'descripcion' => 'I35'],
    'SIGA' => ['check' => 'H36', 'descripcion' => 'I36'],
    'SISTEMA REGISTRO CIVIL' => ['check' => 'H37', 'descripcion' => 'I37'],
    'RUBEM' => ['check' => 'H38', 'descripcion' => 'I38'],
    'RUB PVL 20' => ['check' => 'H39', 'descripcion' => 'I39'],
    'SISPLA' => ['check' => 'H40', 'descripcion' => 'I40'],
    'SISTEMA VÍA WEB' => ['check' => 'H41', 'descripcion' => 'I41'],
    'OTROS' => ['check' => 'H42', 'descripcion' => 'I42'],
  ],
  'software' => [
    'SISTEMA OPERATIVO' => ['check' => 'C46', 'descripcion' => 'D46'],
    'WORD' => ['check' => 'C47', 'descripcion' => 'D47'],
    'EXCEL' => ['check' => 'C48', 'descripcion' => 'D48'],
    'POWER POINT' => ['check' => 'C49', 'descripcion' => 'D49'],
    'INTERNET' => ['check' => 'C50', 'descripcion' => 'D50'],
    'ANTIVIRUS' => ['check' => 'C51', 'descripcion' => 'D51'],
    'OTROS' => ['check' => 'C52', 'descripcion' => 'D52'],
  ]
];


// Obtener nuevo número para ficha_control (puedes adaptar a tu lógica)
$stmt = $conexion->query("SELECT COALESCE(MAX(Numero), 0) + 1 AS nuevo_num FROM tb_ficha_control");
$numeroNuevo = (int)$stmt->fetchColumn();
$numeroFormateado = str_pad($numeroNuevo, 6, '0', STR_PAD_LEFT);

// Ruta a la plantilla
$plantillaPath = $_SERVER['DOCUMENT_ROOT'] . '/sisti/backend/php/excel/plantillas/ficha_instalacion.xlsx';
$spreadsheet = IOFactory::load($plantillaPath);
$sheet = $spreadsheet->getActiveSheet();

// Colocar número de ficha en J3 y centrar horizontalmente
$sheet->setCellValue('J3', 'N° ' . $numeroFormateado);
$sheet->getStyle('J3')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);

// Rellenar las celdas
$sheet->setCellValue('C8', $unidadOrganica);
$sheet->setCellValue('J8', $fechaHoy);

date_default_timezone_set('America/Lima'); // Asegura la zona horaria correcta
$sheet->setCellValue('J10', date('H:i:s'));

$sheet->setCellValue('C11', $trabajador);
$sheet->setCellValue('C14', $cargo);
$sheet->setCellValue('J14', $dni);
$sheet->setCellValue('C22', $requerimiento);
$sheet->setCellValue('C24', $tecnico);

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

// Proteger hoja
$sheet->getProtection()->setSheet(true);
$sheet->getProtection()->setSort(true);
$sheet->getProtection()->setInsertRows(true);
$sheet->getProtection()->setFormatCells(false);
$sheet->getProtection()->setPassword('CuandoPagan');

// Preparar ruta y nombre para guardar el archivo en servidor
$filename = "ficha_Instalacion_" . $numeroFormateado . ".xlsx";

// Carpeta donde guardar (asegúrate que exista y tenga permisos)
$carpetaGuardar = $_SERVER['DOCUMENT_ROOT'] . "/sisti/archivos/fichas/";
if (!is_dir($carpetaGuardar)) {
  mkdir($carpetaGuardar, 0777, true);
}

$rutaCompleta = $carpetaGuardar . $filename;
$rutaRelativa = "/sisti/archivos/fichas/" . $filename;

// Guardar el archivo en disco
$writer = new Xlsx($spreadsheet);
$writer->save($rutaCompleta);

// Guardar registro en base de datos
$fechaActual = date('Y-m-d H:i:s');

$sqlInsert = "INSERT INTO tb_ficha_control (Numero, Id_Usuarios, ArchivoExcel, Fecha)
              VALUES (:numero, :id_usuario, :rutaArchivo, :fecha)";
$stmt = $conexion->prepare($sqlInsert);
$stmt->bindParam(':numero', $numeroNuevo, PDO::PARAM_INT);
$stmt->bindParam(':id_usuario', $idUsuario, PDO::PARAM_INT);
$stmt->bindParam(':rutaArchivo', $rutaRelativa, PDO::PARAM_STR);
$stmt->bindParam(':fecha', $fechaActual, PDO::PARAM_STR);
$stmt->execute();

// Descargar el archivo al navegador
header("Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet");
header("Content-Disposition: attachment; filename=\"$filename\"");
header("Cache-Control: max-age=0");

readfile($rutaCompleta);
exit;
