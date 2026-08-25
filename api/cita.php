<?php
/**
 * Appointment request endpoint.
 * Accepts JSON or form POST and stores the request in storage/citas.json.
 */

declare(strict_types=1);

header('Content-Type: application/json; charset=utf-8');
header('Cache-Control: no-store');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['ok' => false, 'message' => 'Método no permitido. Usa POST.'], JSON_UNESCAPED_UNICODE);
    exit;
}

$contentType = $_SERVER['CONTENT_TYPE'] ?? $_SERVER['HTTP_CONTENT_TYPE'] ?? '';
$input = [];

if (stripos($contentType, 'application/json') !== false) {
    $raw = file_get_contents('php://input');
    $decoded = json_decode($raw ?: '[]', true);
    $input = is_array($decoded) ? $decoded : [];
} else {
    $input = $_POST;
}

$nombre = trim((string) ($input['nombre'] ?? $input['name'] ?? ''));
$telefono = trim((string) ($input['telefono'] ?? $input['phone'] ?? ''));
$servicio = trim((string) ($input['servicio'] ?? $input['service'] ?? ''));
$mensaje = trim((string) ($input['mensaje'] ?? $input['message'] ?? ''));

$allowedServices = [
    'apendicitis',
    'colecistitis',
    'hernias',
    'laparoscopica',
    'toracica',
    'otro',
];

$errors = [];

if ($nombre === '' || mb_strlen($nombre) < 3) {
    $errors['nombre'] = 'Ingresa tu nombre completo.';
}

$phoneDigits = preg_replace('/\D+/', '', $telefono) ?? '';
if ($phoneDigits === '' || strlen($phoneDigits) < 7) {
    $errors['telefono'] = 'Ingresa un teléfono válido.';
}

if ($servicio === '' || !in_array($servicio, $allowedServices, true)) {
    $errors['servicio'] = 'Selecciona un motivo de consulta.';
}

if (mb_strlen($mensaje) > 1000) {
    $errors['mensaje'] = 'El mensaje es demasiado largo.';
}

if ($errors !== []) {
    http_response_code(422);
    echo json_encode([
        'ok' => false,
        'message' => 'Revisa los datos del formulario.',
        'errors' => $errors,
    ], JSON_UNESCAPED_UNICODE);
    exit;
}

require_once dirname(__DIR__) . '/config/db.php';

$record = [
    'id' => bin2hex(random_bytes(8)),
    'nombre' => $nombre,
    'telefono' => $telefono,
    'servicio' => $servicio,
    'mensaje' => $mensaje,
    'created_at' => date('c'),
    'ip' => $_SERVER['REMOTE_ADDR'] ?? null,
];

$saved = false;
$pdo = db();
if ($pdo instanceof PDO) {
    try {
        $svcStmt = $pdo->prepare('SELECT id FROM servicios WHERE slug = ? AND (deleted_at IS NULL) LIMIT 1');
        $svcStmt->execute([$servicio]);
        $servicioId = $svcStmt->fetchColumn();
        $servicioId = $servicioId !== false ? (int) $servicioId : null;

        $ins = $pdo->prepare(
            'INSERT INTO citas (
                servicio_id, nombre_contacto, telefono_contacto, mensaje,
                estado, origen, ip, user_agent
             ) VALUES (?, ?, ?, ?, ?, ?, ?, ?)'
        );
        $ua = substr((string) ($_SERVER['HTTP_USER_AGENT'] ?? ''), 0, 255);
        $ins->execute([
            $servicioId,
            $nombre,
            $telefono,
            $mensaje !== '' ? $mensaje : null,
            'pendiente',
            'web_publica',
            $record['ip'],
            $ua !== '' ? $ua : null,
        ]);
        $saved = true;
    } catch (Throwable $e) {
        $saved = false;
    }
}

if (!$saved) {
    $storageDir = dirname(__DIR__) . '/storage';
    $storageFile = $storageDir . '/citas.json';

    if (!is_dir($storageDir) && !mkdir($storageDir, 0755, true) && !is_dir($storageDir)) {
        http_response_code(500);
        echo json_encode(['ok' => false, 'message' => 'No se pudo crear el almacenamiento.'], JSON_UNESCAPED_UNICODE);
        exit;
    }

    $existing = [];
    if (is_file($storageFile)) {
        $json = file_get_contents($storageFile);
        $parsed = json_decode($json ?: '[]', true);
        $existing = is_array($parsed) ? $parsed : [];
    }

    $existing[] = $record;

    if (file_put_contents($storageFile, json_encode($existing, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT), LOCK_EX) === false) {
        http_response_code(500);
        echo json_encode(['ok' => false, 'message' => 'No se pudo guardar la solicitud.'], JSON_UNESCAPED_UNICODE);
        exit;
    }
}

http_response_code(201);
echo json_encode([
    'ok' => true,
    'message' => '¡Solicitud recibida! Te contactaremos pronto para confirmar tu cita.',
    'id' => $record['id'],
], JSON_UNESCAPED_UNICODE);
