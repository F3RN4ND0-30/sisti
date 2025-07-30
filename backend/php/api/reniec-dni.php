<?php
header('Content-Type: application/json');

// Configuración del servicio
$numruc = '20148421103';
$numdniusu = '71864348'; // DNI del funcionario autorizado
$password = '71864348'; // Clave de acceso real o token proporcionado por la entidad
$maxIntentos = 5;
$intentos = 0;

// Leer JSON crudo del body
$input = json_decode(file_get_contents('php://input'), true);

// Validar input
if (!isset($input['numdni']) || !preg_match('/^\d{8}$/', $input['numdni'])) {
    echo json_encode(['status' => 'error', 'message' => 'DNI inválido']);
    exit;
}

$numdni = $input['numdni'];
$datosValidos = false;

while ($intentos < $maxIntentos && !$datosValidos) {
    $intentos++;

    // Construir URL con parámetros codificados
    $url = "https://ws2.pide.gob.pe/Rest/RENIEC/Consultar?" . http_build_query([
        'nuDniConsulta' => $numdni,
        'nuDniUsuario' => $numdniusu,
        'nuRucUsuario' => $numruc,
        'password' => $password,
        'out' => 'json'
    ]);

    // Opcional: usar context para mejorar el manejo de errores
    $context = stream_context_create([
        'http' => [
            'method' => 'GET',
            'timeout' => 5
        ]
    ]);

    $response = @file_get_contents($url, false, $context);

    if ($response === false) {
        sleep(1);
        continue;
    }

    $data = json_decode($response, true);
    $persona = $data['consultarResponse']['return']['datosPersona'] ?? null;

    // Validar respuesta
    if (
        $persona &&
        !empty($persona['apPrimer']) &&
        !empty($persona['apSegundo']) &&
        !empty($persona['prenombres']) &&
        !empty($persona['direccion']) &&
        $persona['direccion'] !== '-'
    ) {
        $datosValidos = true;

        echo json_encode([
            'status' => 'success',
            'numDNI' => $numdni,
            'apPrimer' => $persona['apPrimer'],
            'apSegundo' => $persona['apSegundo'],
            'prenombres' => $persona['prenombres'],
            'direccion' => $persona['direccion'],
            'intentos' => $intentos
        ]);
        exit;
    }

    sleep(1); // Espera entre intentos
}

// Si no se logró obtener datos válidos
echo json_encode([
    'status' => 'error',
    'message' => "No se pudieron obtener datos completos para el DNI $numdni después de $maxIntentos intentos."
]);
exit;
