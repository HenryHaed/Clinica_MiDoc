-- Servicios Adultos / Pediátrica (BD ya existentes). Ejecutar UNA VEZ.
-- También se aplica automáticamente al abrir la landing (servicios_ensure_catalog).

-- Columna categoria
SET @has_categoria := (
  SELECT COUNT(*) FROM INFORMATION_SCHEMA.COLUMNS
  WHERE TABLE_SCHEMA = DATABASE()
    AND TABLE_NAME = 'servicios'
    AND COLUMN_NAME = 'categoria'
);
SET @sql_categoria := IF(
  @has_categoria = 0,
  'ALTER TABLE servicios ADD COLUMN categoria VARCHAR(40) NOT NULL DEFAULT ''adultos'' AFTER imagen_url, ADD KEY idx_servicios_categoria (categoria)',
  'SELECT 1'
);
PREPARE stmt_categoria FROM @sql_categoria;
EXECUTE stmt_categoria;
DEALLOCATE PREPARE stmt_categoria;

-- Títulos CMS
INSERT INTO cms_contenidos (seccion, clave, tipo, valor, etiqueta, updated_by)
SELECT 'servicios', 'servicios.titulo_adultos', 'texto', 'Cirugía General Adultos', 'Título bloque adultos', NULL
WHERE NOT EXISTS (SELECT 1 FROM cms_contenidos WHERE clave = 'servicios.titulo_adultos' LIMIT 1);

INSERT INTO cms_contenidos (seccion, clave, tipo, valor, etiqueta, updated_by)
SELECT 'servicios', 'servicios.titulo_pediatrica', 'texto', 'Cirugía Pediátrica', 'Título bloque pediátrica', NULL
WHERE NOT EXISTS (SELECT 1 FROM cms_contenidos WHERE clave = 'servicios.titulo_pediatrica' LIMIT 1);

UPDATE cms_contenidos
SET valor = 'Atención en Cirugía General Adultos y Niños',
    etiqueta = 'Título sección servicios',
    updated_at = CURRENT_TIMESTAMP
WHERE clave = 'servicios.titulo'
  AND valor IN ('Cirugía General', 'Cirugía general', 'Servicios disponibles en Cirugía General Adultos y Niños', '');

UPDATE cms_contenidos
SET valor = 'Procedimientos que ofrece el Dr. Hernán Caihuara Salas en Cirugía General Adultos y Cirugía Pediátrica.',
    updated_at = CURRENT_TIMESTAMP
WHERE clave = 'servicios.subtitulo'
  AND (
    valor = ''
    OR valor IS NULL
    OR valor LIKE 'Procedimientos quirúrgicos especializados%'
  );

-- Catálogo nuevo (solo inserta si el slug no existe)
INSERT INTO servicios (slug, titulo, descripcion, icono, imagen_url, categoria, orden, activo, destacado)
SELECT * FROM (
  SELECT 'adulto-apendicitis' AS slug, 'Apendicitis' AS titulo, 'Tratamiento quirúrgico de la apendicitis aguda en adultos.' AS descripcion, 'medical_services' AS icono, NULL AS imagen_url, 'adultos' AS categoria, 10 AS orden, 1 AS activo, 0 AS destacado
  UNION ALL SELECT 'adulto-colecistitis-litiasica', 'Colecistitis litiásica', 'Cirugía de vesícula por colecistitis litiásica.', 'healing', NULL, 'adultos', 20, 1, 0
  UNION ALL SELECT 'adulto-hernias', 'Hernias', 'Reparación de hernias abdominales e inguinales en adultos.', 'accessibility_new', NULL, 'adultos', 30, 1, 0
  UNION ALL SELECT 'adulto-trauma-toracico', 'Trauma torácico', 'Atención quirúrgica de traumatismos del tórax.', 'pulmonology', NULL, 'adultos', 40, 1, 0
  UNION ALL SELECT 'adulto-trauma-abdominal', 'Trauma abdominal', 'Manejo quirúrgico del trauma abdominal.', 'emergency', NULL, 'adultos', 50, 1, 0
  UNION ALL SELECT 'adulto-cirugia-laparoscopica', 'Cirugía laparoscópica', 'Procedimientos mínimamente invasivos en adultos.', 'biotech', NULL, 'adultos', 60, 1, 0
  UNION ALL SELECT 'adulto-ecografia-abdominal', 'Ecografía abdominal', 'Evaluación diagnóstica por ecografía abdominal.', 'radiology', NULL, 'adultos', 70, 1, 0
  UNION ALL SELECT 'adulto-endoscopia-digestiva', 'Endoscopia digestiva', 'Estudios y procedimientos de endoscopia digestiva.', 'gastroenterology', NULL, 'adultos', 80, 1, 0
  UNION ALL SELECT 'pediatrico-apendicitis', 'Apendicitis', 'Cirugía de apendicitis en pacientes pediátricos.', 'medical_services', NULL, 'pediatrica', 110, 1, 0
  UNION ALL SELECT 'pediatrico-colecistitis-litiasica', 'Colecistitis litiásica', 'Manejo quirúrgico de colecistitis litiásica en niños.', 'healing', NULL, 'pediatrica', 120, 1, 0
  UNION ALL SELECT 'pediatrico-hernias-labio-leporino', 'Hernias, labio leporino', 'Reparación de hernias y labio leporino en pediatría.', 'child_care', NULL, 'pediatrica', 130, 1, 0
  UNION ALL SELECT 'pediatrico-criptorquidia-fimosis', 'Criptorquidia, fimosis', 'Cirugía pediátrica de criptorquidia y fimosis.', 'pediatrics', NULL, 'pediatrica', 140, 1, 0
  UNION ALL SELECT 'pediatrico-trauma-abdominal', 'Trauma abdominal', 'Atención del trauma abdominal en niños.', 'emergency', NULL, 'pediatrica', 150, 1, 0
  UNION ALL SELECT 'pediatrico-cirugia-laparoscopica', 'Cirugía laparoscópica', 'Cirugía laparoscópica pediátrica.', 'biotech', NULL, 'pediatrica', 160, 1, 0
  UNION ALL SELECT 'pediatrico-cirugia-neonatal', 'Cirugía neonatal', 'Procedimientos quirúrgicos en neonatos.', 'crib', NULL, 'pediatrica', 170, 1, 0
  UNION ALL SELECT 'pediatrico-malformaciones', 'Malformaciones', 'Corrección quirúrgica de malformaciones congénitas.', 'diversity_1', NULL, 'pediatrica', 180, 1, 0
) AS seed
WHERE NOT EXISTS (SELECT 1 FROM servicios s WHERE s.slug = seed.slug LIMIT 1);

-- Asegurar "otro" con categoría
UPDATE servicios SET categoria = 'adultos', orden = 999 WHERE slug = 'otro' AND deleted_at IS NULL;

-- Desactivar listado plano antiguo
UPDATE servicios
SET activo = 0
WHERE deleted_at IS NULL
  AND slug IN ('apendicitis', 'colecistitis', 'hernias', 'laparoscopica', 'toracica');
