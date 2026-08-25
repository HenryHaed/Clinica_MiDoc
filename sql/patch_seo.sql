-- Actualiza textos SEO si ya tenías la BD (opcional).
-- Ejecutar en phpMyAdmin.

INSERT INTO cms_contenidos (seccion, clave, tipo, valor, etiqueta, updated_by) VALUES
('seo', 'seo.keywords', 'texto',
 'MiDoc, cirugía general, cirugía pediátrica, Dr Hernán Caihuara, Clínica Saint Jude, cirujano, laparoscópica',
 'Palabras clave SEO', 1)
ON DUPLICATE KEY UPDATE etiqueta = VALUES(etiqueta);

UPDATE cms_contenidos
SET valor = 'MiDoc - Cirugía General y Pediátrica | Dr. Hernán Caihuara',
    etiqueta = 'Título SEO (Google)'
WHERE clave = 'seo.titulo'
  AND (valor = 'MiDoc - Salud Primordial' OR valor = '');

UPDATE cms_contenidos
SET valor = 'Clínica MiDoc. Dr. Hernán Caihuara Salas, Cirujano General Adultos y Cirujano Pediatra en Clínica Saint Jude. Agenda tu cita.',
    etiqueta = 'Descripción SEO (Google)'
WHERE clave = 'seo.descripcion'
  AND (valor LIKE 'Dr. Hernán Caihuara Salas. Cirujano General%' OR valor = '');
