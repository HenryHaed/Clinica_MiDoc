<?php
/**
 * Recuperación de contraseña por código SMTP.
 */
declare(strict_types=1);

require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/mailer.php';
require_once __DIR__ . '/auth.php';

const PASSWORD_RESET_TTL_MINUTES = 15;
const PASSWORD_RESET_MAX_ATTEMPTS = 5;
const PASSWORD_RESET_COOLDOWN_SECONDS = 60;

/**
 * Mensaje genérico (no revela si el correo existe).
 */
function password_reset_generic_ok(): string
{
    return 'Si el correo está registrado, te enviamos un código de verificación. Revisa tu bandeja de entrada y spam.';
}

/**
 * @return array{ok:bool,message:string,step?:string}
 */
function password_reset_request(string $email): array
{
    $email = strtolower(trim($email));
    $generic = password_reset_generic_ok();

    if ($email === '' || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
        return ['ok' => false, 'message' => 'Ingresa un correo válido.'];
    }

    $last = (int) ($_SESSION['_pwreset_last'] ?? 0);
    if ($last > 0 && (time() - $last) < PASSWORD_RESET_COOLDOWN_SECONDS) {
        return ['ok' => false, 'message' => 'Espera un momento antes de solicitar otro código.'];
    }

    if (mailer_config() === null) {
        return ['ok' => false, 'message' => 'El envío de correo no está configurado. Contacta al administrador.'];
    }

    $pdo = db();
    if (!$pdo instanceof PDO) {
        return ['ok' => false, 'message' => 'No hay conexión a la base de datos.'];
    }

    // Asegura tabla (instalaciones previas)
    password_reset_ensure_table($pdo);

    $stmt = $pdo->prepare(
        'SELECT u.id, u.email, u.nombre, u.activo, u.deleted_at, r.slug AS rol_slug, r.activo AS rol_activo
         FROM usuarios u
         INNER JOIN roles r ON r.id = u.rol_id
         WHERE u.email = ?
         LIMIT 1'
    );
    $stmt->execute([$email]);
    $row = $stmt->fetch();

    $_SESSION['_pwreset_last'] = time();

    $eligible = is_array($row)
        && (int) $row['activo'] === 1
        && $row['deleted_at'] === null
        && (int) $row['rol_activo'] === 1
        && in_array((string) $row['rol_slug'], AUTH_PANEL_ROLES, true);

    if (!$eligible) {
        $_SESSION['pwreset'] = [
            'email' => $email,
            'usuario_id' => 0,
            'verified' => false,
            'started_at' => time(),
        ];
        return ['ok' => true, 'message' => $generic, 'step' => 'code'];
    }

    $code = (string) random_int(100000, 999999);
    $hash = password_hash($code, PASSWORD_DEFAULT);
    $expires = (new DateTimeImmutable('now'))->modify('+' . PASSWORD_RESET_TTL_MINUTES . ' minutes');

    $pdo->prepare('UPDATE password_resets SET used_at = NOW() WHERE usuario_id = ? AND used_at IS NULL')
        ->execute([(int) $row['id']]);

    $ins = $pdo->prepare(
        'INSERT INTO password_resets (usuario_id, email, code_hash, attempts, expires_at, ip)
         VALUES (?, ?, ?, 0, ?, ?)'
    );
    $ins->execute([
        (int) $row['id'],
        (string) $row['email'],
        $hash,
        $expires->format('Y-m-d H:i:s'),
        $_SERVER['REMOTE_ADDR'] ?? null,
    ]);

    $nombre = (string) $row['nombre'];
    $mins = PASSWORD_RESET_TTL_MINUTES;
    $html = '<div style="font-family:Arial,sans-serif;max-width:520px;margin:0 auto;color:#14352F">'
        . '<h2 style="color:#169A8A">Recuperar contraseña — MiDoc</h2>'
        . '<p>Hola ' . htmlspecialchars($nombre, ENT_QUOTES, 'UTF-8') . ',</p>'
        . '<p>Tu código de verificación es:</p>'
        . '<p style="font-size:32px;letter-spacing:8px;font-weight:bold;color:#003E67">' . htmlspecialchars($code, ENT_QUOTES, 'UTF-8') . '</p>'
        . '<p>Caduca en <strong>' . $mins . ' minutos</strong>. Si no solicitaste este cambio, ignora este correo.</p>'
        . '</div>';
    $text = "Hola {$nombre},\n\nTu código MiDoc es: {$code}\nCaduca en {$mins} minutos.\n";

    $sent = mailer_send((string) $row['email'], 'Código de recuperación MiDoc', $html, $text);
    if (!$sent['ok']) {
        return ['ok' => false, 'message' => $sent['error'] ?? 'No se pudo enviar el correo.'];
    }

    $_SESSION['pwreset'] = [
        'email' => (string) $row['email'],
        'usuario_id' => (int) $row['id'],
        'verified' => false,
        'started_at' => time(),
    ];

    return ['ok' => true, 'message' => $generic, 'step' => 'code'];
}

/**
 * @return array{ok:bool,message:string,step?:string}
 */
function password_reset_verify_code(string $email, string $code): array
{
    $email = strtolower(trim($email));
    $code = preg_replace('/\s+/', '', trim($code)) ?? '';

    if ($email === '' || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
        return ['ok' => false, 'message' => 'Correo inválido.'];
    }
    if (!preg_match('/^\d{6}$/', $code)) {
        return ['ok' => false, 'message' => 'El código debe tener 6 dígitos.'];
    }

    $pdo = db();
    if (!$pdo instanceof PDO) {
        return ['ok' => false, 'message' => 'No hay conexión a la base de datos.'];
    }
    password_reset_ensure_table($pdo);

    $stmt = $pdo->prepare(
        'SELECT id, usuario_id, email, code_hash, attempts, expires_at, used_at
         FROM password_resets
         WHERE email = ? AND used_at IS NULL
         ORDER BY id DESC
         LIMIT 1'
    );
    $stmt->execute([$email]);
    $row = $stmt->fetch();

    if (!is_array($row)) {
        return ['ok' => false, 'message' => 'Código inválido o expirado. Solicita uno nuevo.'];
    }

    if ((int) $row['attempts'] >= PASSWORD_RESET_MAX_ATTEMPTS) {
        return ['ok' => false, 'message' => 'Demasiados intentos. Solicita un código nuevo.'];
    }

    $expires = strtotime((string) $row['expires_at']);
    if ($expires === false || $expires < time()) {
        return ['ok' => false, 'message' => 'El código expiró. Solicita uno nuevo.'];
    }

    if (!password_verify($code, (string) $row['code_hash'])) {
        $pdo->prepare('UPDATE password_resets SET attempts = attempts + 1 WHERE id = ?')
            ->execute([(int) $row['id']]);
        return ['ok' => false, 'message' => 'Código incorrecto.'];
    }

    $_SESSION['pwreset'] = [
        'email' => (string) $row['email'],
        'usuario_id' => (int) $row['usuario_id'],
        'reset_id' => (int) $row['id'],
        'verified' => true,
        'verified_at' => time(),
    ];

    return ['ok' => true, 'message' => 'Código verificado. Define tu nueva contraseña.', 'step' => 'password'];
}

/**
 * @return array{ok:bool,message:string,step?:string}
 */
function password_reset_set_password(string $password, string $password2): array
{
    $session = $_SESSION['pwreset'] ?? null;
    if (!is_array($session) || empty($session['verified']) || empty($session['usuario_id']) || empty($session['reset_id'])) {
        return ['ok' => false, 'message' => 'Sesión de recuperación inválida. Empieza de nuevo.', 'step' => 'email'];
    }

    if (strlen($password) < 8) {
        return ['ok' => false, 'message' => 'La contraseña debe tener al menos 8 caracteres.'];
    }
    if ($password !== $password2) {
        return ['ok' => false, 'message' => 'Las contraseñas no coinciden.'];
    }

    $pdo = db();
    if (!$pdo instanceof PDO) {
        return ['ok' => false, 'message' => 'No hay conexión a la base de datos.'];
    }

    $check = $pdo->prepare(
        'SELECT id FROM password_resets
         WHERE id = ? AND usuario_id = ? AND used_at IS NULL AND expires_at >= NOW()
         LIMIT 1'
    );
    $check->execute([(int) $session['reset_id'], (int) $session['usuario_id']]);
    if (!$check->fetch()) {
        unset($_SESSION['pwreset']);
        return ['ok' => false, 'message' => 'El código ya no es válido. Solicita uno nuevo.', 'step' => 'email'];
    }

    $hash = password_hash($password, PASSWORD_DEFAULT);
    $pdo->beginTransaction();
    try {
        $pdo->prepare('UPDATE usuarios SET password_hash = ?, updated_at = NOW() WHERE id = ?')
            ->execute([$hash, (int) $session['usuario_id']]);
        $pdo->prepare('UPDATE password_resets SET used_at = NOW() WHERE id = ?')
            ->execute([(int) $session['reset_id']]);
        $pdo->prepare('UPDATE password_resets SET used_at = NOW() WHERE usuario_id = ? AND used_at IS NULL')
            ->execute([(int) $session['usuario_id']]);

        try {
            $audit = $pdo->prepare(
                'INSERT INTO audit_log (usuario_id, accion, tabla_afectada, registro_id, payload_json, ip)
                 VALUES (?, ?, ?, ?, ?, ?)'
            );
            $audit->execute([
                (int) $session['usuario_id'],
                'password_reset',
                'usuarios',
                (string) $session['usuario_id'],
                json_encode(['via' => 'email_code'], JSON_UNESCAPED_UNICODE),
                $_SERVER['REMOTE_ADDR'] ?? null,
            ]);
        } catch (Throwable $e) {
            // no bloquear
        }

        $pdo->commit();
    } catch (Throwable $e) {
        $pdo->rollBack();
        return ['ok' => false, 'message' => 'No se pudo actualizar la contraseña.'];
    }

    unset($_SESSION['pwreset'], $_SESSION['_pwreset_last']);
    return ['ok' => true, 'message' => 'Contraseña actualizada. Ya puedes iniciar sesión.', 'step' => 'done'];
}

function password_reset_clear(): void
{
    unset($_SESSION['pwreset']);
}

function password_reset_ensure_table(PDO $pdo): void
{
    static $done = false;
    if ($done) {
        return;
    }
    $pdo->exec(
        'CREATE TABLE IF NOT EXISTS password_resets (
          id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
          usuario_id INT UNSIGNED NOT NULL,
          email VARCHAR(190) NOT NULL,
          code_hash VARCHAR(255) NOT NULL,
          attempts TINYINT UNSIGNED NOT NULL DEFAULT 0,
          expires_at DATETIME NOT NULL,
          used_at DATETIME NULL,
          ip VARCHAR(45) NULL,
          created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
          PRIMARY KEY (id),
          KEY idx_password_resets_email (email),
          KEY idx_password_resets_usuario (usuario_id),
          KEY idx_password_resets_expires (expires_at)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci'
    );
    $done = true;
}
