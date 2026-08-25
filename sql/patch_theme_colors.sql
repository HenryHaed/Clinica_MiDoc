-- Patch: colores de marca editables (BD ya existentes)
-- Ejecutar una sola vez si la BD ya estaba creada.

INSERT INTO cms_contenidos (seccion, clave, tipo, valor, etiqueta, updated_by)
SELECT 'theme', 'theme.cyan', 'color', '#003E67', 'Cian / azul principal', NULL
WHERE NOT EXISTS (SELECT 1 FROM cms_contenidos WHERE clave = 'theme.cyan' LIMIT 1);

INSERT INTO cms_contenidos (seccion, clave, tipo, valor, etiqueta, updated_by)
SELECT 'theme', 'theme.yellow', 'color', '#F9D423', 'Amarillo (botones CTA)', NULL
WHERE NOT EXISTS (SELECT 1 FROM cms_contenidos WHERE clave = 'theme.yellow' LIMIT 1);

INSERT INTO cms_contenidos (seccion, clave, tipo, valor, etiqueta, updated_by)
SELECT 'theme', 'theme.orange', 'color', '#FF9100', 'Naranja (acento)', NULL
WHERE NOT EXISTS (SELECT 1 FROM cms_contenidos WHERE clave = 'theme.orange' LIMIT 1);

INSERT INTO cms_contenidos (seccion, clave, tipo, valor, etiqueta, updated_by)
SELECT 'theme', 'theme.teal', 'color', '#169A8A', 'Teal / verde primario', NULL
WHERE NOT EXISTS (SELECT 1 FROM cms_contenidos WHERE clave = 'theme.teal' LIMIT 1);

INSERT INTO cms_contenidos (seccion, clave, tipo, valor, etiqueta, updated_by)
SELECT 'theme', 'theme.mint', 'color', '#3DE2AD', 'Menta', NULL
WHERE NOT EXISTS (SELECT 1 FROM cms_contenidos WHERE clave = 'theme.mint' LIMIT 1);

INSERT INTO cms_contenidos (seccion, clave, tipo, valor, etiqueta, updated_by)
SELECT 'theme', 'theme.sage', 'color', '#B2D9C2', 'Sage (bordes)', NULL
WHERE NOT EXISTS (SELECT 1 FROM cms_contenidos WHERE clave = 'theme.sage' LIMIT 1);

INSERT INTO cms_contenidos (seccion, clave, tipo, valor, etiqueta, updated_by)
SELECT 'theme', 'theme.mist', 'color', '#D9EEE1', 'Fondo (mist)', NULL
WHERE NOT EXISTS (SELECT 1 FROM cms_contenidos WHERE clave = 'theme.mist' LIMIT 1);

INSERT INTO cms_contenidos (seccion, clave, tipo, valor, etiqueta, updated_by)
SELECT 'theme', 'theme.text', 'color', '#14352F', 'Texto principal', NULL
WHERE NOT EXISTS (SELECT 1 FROM cms_contenidos WHERE clave = 'theme.text' LIMIT 1);

INSERT INTO cms_contenidos (seccion, clave, tipo, valor, etiqueta, updated_by)
SELECT 'theme', 'theme.text_muted', 'color', '#3A5F56', 'Texto secundario', NULL
WHERE NOT EXISTS (SELECT 1 FROM cms_contenidos WHERE clave = 'theme.text_muted' LIMIT 1);
