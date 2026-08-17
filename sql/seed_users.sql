-- =============================================================================
-- Semilla de usuarios de panel (ejecutar en phpMyAdmin sobre clinica_mi_doc)
-- Contraseña para TODOS: Password123$
-- Hash: bcrypt PASSWORD_DEFAULT
-- =============================================================================

USE clinica_mi_doc;

-- Actualiza el super admin existente
UPDATE usuarios
SET
  password_hash = '$2y$10$8aR1KUc.wHLvs1oS.O5XKe/4mY3V4ikXpoq6a7CNfTQcRT9ZFrCnu',
  nombre = 'Super Admin MiDoc',
  activo = 1,
  deleted_at = NULL
WHERE email = 'admin@clinicamidoc.com';

-- Usuarios por rol (INSERT o UPDATE si ya existen)
INSERT INTO usuarios (rol_id, email, password_hash, nombre, activo) VALUES
(2, 'gestor@clinicamidoc.com', '$2y$10$8aR1KUc.wHLvs1oS.O5XKe/4mY3V4ikXpoq6a7CNfTQcRT9ZFrCnu', 'Admin Gestión', 1),
(3, 'doctor@clinicamidoc.com', '$2y$10$8aR1KUc.wHLvs1oS.O5XKe/4mY3V4ikXpoq6a7CNfTQcRT9ZFrCnu', 'Dr. Hernán Caihuara', 1),
(4, 'enfermero@clinicamidoc.com', '$2y$10$8aR1KUc.wHLvs1oS.O5XKe/4mY3V4ikXpoq6a7CNfTQcRT9ZFrCnu', 'Enfermería MiDoc', 1),
(5, 'recepcion@clinicamidoc.com', '$2y$10$8aR1KUc.wHLvs1oS.O5XKe/4mY3V4ikXpoq6a7CNfTQcRT9ZFrCnu', 'Recepción MiDoc', 1)
ON DUPLICATE KEY UPDATE
  password_hash = VALUES(password_hash),
  nombre = VALUES(nombre),
  rol_id = VALUES(rol_id),
  activo = 1,
  deleted_at = NULL;

-- Vincular el doctor de personal con su usuario de login (si existe)
UPDATE personal p
INNER JOIN usuarios u ON u.email = 'doctor@clinicamidoc.com'
SET p.usuario_id = u.id
WHERE p.id = 1;

-- Verificar
SELECT u.id, u.email, u.nombre, r.slug AS rol, u.activo
FROM usuarios u
INNER JOIN roles r ON r.id = u.rol_id
ORDER BY u.id;
