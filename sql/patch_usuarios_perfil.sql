-- Perfil de usuarios: apellidos + datos extras
-- Si alguna columna ya existe, ignora el error de "Duplicate column".
-- (También se crean solas al abrir usuarios.php)

ALTER TABLE usuarios ADD COLUMN apellido_paterno VARCHAR(80) NULL AFTER nombre;
ALTER TABLE usuarios ADD COLUMN apellido_materno VARCHAR(80) NULL AFTER apellido_paterno;
ALTER TABLE usuarios ADD COLUMN datos_extras TEXT NULL AFTER apellido_materno;
