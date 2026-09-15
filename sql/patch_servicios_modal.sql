-- Detalle largo del servicio (modal en la landing)
-- Si la columna ya existe, ignora el error Duplicate column.

ALTER TABLE servicios
  ADD COLUMN detalle TEXT NULL AFTER descripcion;

UPDATE cms_contenidos
SET valor = 'Atención en Cirugía General Adultos y Niños',
    etiqueta = 'Título sección servicios',
    updated_at = CURRENT_TIMESTAMP
WHERE clave = 'servicios.titulo'
  AND valor IN (
    'Cirugía General',
    'Cirugía general',
    'Servicios disponibles en Cirugía General Adultos y Niños',
    ''
  );
