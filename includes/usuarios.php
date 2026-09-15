<?php
/**
 * Gestión de usuarios del panel (perfil + contraseña).
 */
declare(strict_types=1);

require_once __DIR__ . '/../config/db.php';

/**
 * Asegura columnas de perfil en usuarios (instalaciones ya existentes).
 */
function usuarios_ensure_profile_columns(): void
{
    static $done = false;
    if ($done) {
        return;
    }
    $pdo = db();
    if (!$pdo instanceof PDO) {
        return;
    }

    $cols = [];
    try {
        $rows = $pdo->query('SHOW COLUMNS FROM usuarios')->fetchAll(PDO::FETCH_ASSOC);
        foreach ($rows as $row) {
            $cols[(string) ($row['Field'] ?? '')] = true;
        }
    } catch (Throwable $e) {
        return;
    }

    $alters = [];
    if (!isset($cols['apellido_paterno'])) {
        $alters[] = 'ADD COLUMN apellido_paterno VARCHAR(80) NULL AFTER nombre';
    }
    if (!isset($cols['apellido_materno'])) {
        $alters[] = 'ADD COLUMN apellido_materno VARCHAR(80) NULL AFTER apellido_paterno';
    }
    if (!isset($cols['datos_extras'])) {
        $alters[] = 'ADD COLUMN datos_extras TEXT NULL AFTER apellido_materno';
    }
    foreach ($alters as $fragment) {
        try {
            $pdo->exec('ALTER TABLE usuarios ' . $fragment);
        } catch (Throwable $e) {
            // ya existe o sin permiso
        }
    }
    $done = true;
}

/**
 * @param array{nombre?:string,apellido_paterno?:?string,apellido_materno?:?string} $u
 */
function usuario_display_name(array $u): string
{
    $parts = array_values(array_filter([
        trim((string) ($u['nombre'] ?? '')),
        trim((string) ($u['apellido_paterno'] ?? '')),
        trim((string) ($u['apellido_materno'] ?? '')),
    ], static fn (string $p): bool => $p !== ''));

    return $parts !== [] ? implode(' ', $parts) : 'Usuario';
}

/**
 * ¿El actor puede editar datos/contraseña del objetivo?
 *
 * - super_admin: todos (incluido él mismo)
 * - admin: todos excepto super_admin y cualquier admin (incluido él mismo)
 */
function auth_can_manage_user(array $actor, array $target): bool
{
    $actorRole = (string) ($actor['rol_slug'] ?? '');
    $targetRole = (string) ($target['rol_slug'] ?? '');

    if ($actorRole === 'super_admin') {
        return true;
    }

    if ($actorRole === 'admin') {
        return !in_array($targetRole, ['super_admin', 'admin'], true);
    }

    return false;
}

/**
 * Roles que el actor puede asignar al crear usuarios.
 * El super_admin es único: nadie puede crear otro.
 *
 * @return list<string>
 */
function auth_creatable_role_slugs(array $actor): array
{
    $actorRole = (string) ($actor['rol_slug'] ?? '');
    if ($actorRole === 'super_admin') {
        return ['admin', 'doctor', 'enfermero', 'recepcion'];
    }
    if ($actorRole === 'admin') {
        return ['doctor', 'enfermero', 'recepcion'];
    }
    return [];
}

function auth_can_create_role(array $actor, string $roleSlug): bool
{
    return in_array($roleSlug, auth_creatable_role_slugs($actor), true);
}

/**
 * @return list<array{id:int,slug:string,nombre:string}>
 */
function roles_creatable_for(array $actor): array
{
    $allowed = auth_creatable_role_slugs($actor);
    if ($allowed === []) {
        return [];
    }
    $placeholders = implode(',', array_fill(0, count($allowed), '?'));
    $stmt = db()->prepare(
        "SELECT id, slug, nombre FROM roles
         WHERE activo = 1 AND slug IN ($placeholders)
         ORDER BY FIELD(slug, 'admin','doctor','enfermero','recepcion')"
    );
    $stmt->execute($allowed);
    $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
    return is_array($rows) ? $rows : [];
}

/**
 * @param array{
 *   rol_slug:string,
 *   email:string,
 *   password:string,
 *   nombre:string,
 *   apellido_paterno?:?string,
 *   apellido_materno?:?string,
 *   datos_extras?:?string,
 *   activo?:int
 * } $data
 */
function usuario_create(array $data): int
{
    usuarios_ensure_profile_columns();
    $email = strtolower(trim((string) $data['email']));
    $nombre = trim((string) $data['nombre']);
    $rolSlug = (string) $data['rol_slug'];

    if ($nombre === '') {
        throw new RuntimeException('El nombre es obligatorio.');
    }
    if ($email === '' || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
        throw new RuntimeException('Correo electrónico no válido.');
    }
    if (usuario_email_taken($email)) {
        throw new RuntimeException('Ese correo ya está en uso.');
    }

    $stmtRol = db()->prepare('SELECT id FROM roles WHERE slug = ? AND activo = 1 LIMIT 1');
    $stmtRol->execute([$rolSlug]);
    $rolId = (int) $stmtRol->fetchColumn();
    if ($rolId <= 0) {
        throw new RuntimeException('Rol no válido.');
    }

    $hash = password_hash((string) $data['password'], PASSWORD_DEFAULT);
    $stmt = db()->prepare(
        'INSERT INTO usuarios
          (rol_id, email, password_hash, nombre, apellido_paterno, apellido_materno, datos_extras, activo)
         VALUES (?, ?, ?, ?, ?, ?, ?, ?)'
    );
    $stmt->execute([
        $rolId,
        $email,
        $hash,
        $nombre,
        $data['apellido_paterno'] ?? null,
        $data['apellido_materno'] ?? null,
        $data['datos_extras'] ?? null,
        (int) ($data['activo'] ?? 1),
    ]);

    return (int) db()->lastInsertId();
}

/**
 * @return list<array<string,mixed>>
 */
function usuarios_list_panel(): array
{
    usuarios_ensure_profile_columns();
    $sql = 'SELECT u.id, u.email, u.nombre, u.apellido_paterno, u.apellido_materno, u.datos_extras,
                   u.activo, u.last_login_at, u.created_at, u.rol_id,
                   r.slug AS rol_slug, r.nombre AS rol_nombre
            FROM usuarios u
            INNER JOIN roles r ON r.id = u.rol_id
            WHERE u.deleted_at IS NULL
              AND r.slug IN (\'super_admin\',\'admin\',\'doctor\',\'enfermero\',\'recepcion\')
            ORDER BY FIELD(r.slug, \'super_admin\',\'admin\',\'doctor\',\'enfermero\',\'recepcion\'), u.nombre ASC';
    $stmt = db()->query($sql);
    $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
    return is_array($rows) ? $rows : [];
}

/**
 * @return array<string,mixed>|null
 */
function usuario_find(int $id): ?array
{
    if ($id <= 0) {
        return null;
    }
    usuarios_ensure_profile_columns();
    $stmt = db()->prepare(
        'SELECT u.id, u.email, u.nombre, u.apellido_paterno, u.apellido_materno, u.datos_extras,
                u.activo, u.last_login_at, u.created_at, u.rol_id, u.password_hash,
                r.slug AS rol_slug, r.nombre AS rol_nombre
         FROM usuarios u
         INNER JOIN roles r ON r.id = u.rol_id
         WHERE u.id = ? AND u.deleted_at IS NULL
         LIMIT 1'
    );
    $stmt->execute([$id]);
    $row = $stmt->fetch(PDO::FETCH_ASSOC);
    return is_array($row) ? $row : null;
}

/**
 * @param array{nombre:string,apellido_paterno:?string,apellido_materno:?string,datos_extras:?string,email:string,activo:int} $data
 */
function usuario_update_profile(int $id, array $data): void
{
    usuarios_ensure_profile_columns();
    $stmt = db()->prepare(
        'UPDATE usuarios
         SET nombre = ?, apellido_paterno = ?, apellido_materno = ?, datos_extras = ?,
             email = ?, activo = ?
         WHERE id = ? AND deleted_at IS NULL'
    );
    $stmt->execute([
        $data['nombre'],
        $data['apellido_paterno'],
        $data['apellido_materno'],
        $data['datos_extras'],
        $data['email'],
        $data['activo'],
        $id,
    ]);
}

function usuario_set_password(int $id, string $plainPassword): void
{
    $hash = password_hash($plainPassword, PASSWORD_DEFAULT);
    $stmt = db()->prepare(
        'UPDATE usuarios SET password_hash = ? WHERE id = ? AND deleted_at IS NULL'
    );
    $stmt->execute([$hash, $id]);
}

function usuario_email_taken(string $email, int $exceptId = 0): bool
{
    $email = strtolower(trim($email));
    $stmt = db()->prepare(
        'SELECT id FROM usuarios WHERE email = ? AND deleted_at IS NULL AND id <> ? LIMIT 1'
    );
    $stmt->execute([$email, $exceptId]);
    return (bool) $stmt->fetchColumn();
}

function usuario_validate_password(string $password, string $password2): void
{
    if (strlen($password) < 8) {
        throw new RuntimeException('La contraseña debe tener al menos 8 caracteres.');
    }
    if ($password !== $password2) {
        throw new RuntimeException('Las contraseñas no coinciden.');
    }
}

/**
 * Actualiza la sesión si el usuario editado es el logueado.
 *
 * @param array<string,mixed> $row
 */
function usuario_refresh_session_if_self(array $row): void
{
    if (empty($_SESSION['auth']['id']) || (int) $_SESSION['auth']['id'] !== (int) $row['id']) {
        return;
    }
    $_SESSION['auth']['email'] = (string) $row['email'];
    $_SESSION['auth']['nombre'] = usuario_display_name($row);
    $_SESSION['auth']['rol_id'] = (int) $row['rol_id'];
    $_SESSION['auth']['rol_slug'] = (string) $row['rol_slug'];
    $_SESSION['auth']['rol_nombre'] = (string) $row['rol_nombre'];
}
