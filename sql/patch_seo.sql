-- Actualiza título y descripción para el snippet de Google.
-- Ejecutar UNA VEZ en phpMyAdmin (BD ya existente).

UPDATE cms_contenidos
SET valor = 'MiDoc | Cirugía General y Pediátrica — Dr. Hernán Caihuara',
    etiqueta = 'Título SEO (Google)',
    updated_at = CURRENT_TIMESTAMP
WHERE clave = 'seo.titulo';

UPDATE cms_contenidos
SET valor = 'Clínica MiDoc en Saint Jude. Dr. Hernán Caihuara Salas: cirugía general, pediátrica y laparoscópica. Atención profesional. Agenda tu cita al 70688374.',
    etiqueta = 'Descripción SEO (Google)',
    updated_at = CURRENT_TIMESTAMP
WHERE clave = 'seo.descripcion';

UPDATE cms_contenidos
SET valor = 'MiDoc, cirugía general, cirugía pediátrica, Dr Hernán Caihuara, Clínica Saint Jude, cirujano Bolivia, laparoscópica, hernia, apendicitis',
    etiqueta = 'Palabras clave SEO',
    updated_at = CURRENT_TIMESTAMP
WHERE clave = 'seo.keywords';

INSERT INTO cms_contenidos (seccion, clave, tipo, valor, etiqueta, updated_by)
SELECT 'seo', 'seo.keywords', 'texto',
       'MiDoc, cirugía general, cirugía pediátrica, Dr Hernán Caihuara, Clínica Saint Jude, cirujano Bolivia, laparoscópica, hernia, apendicitis',
       'Palabras clave SEO', NULL
WHERE NOT EXISTS (SELECT 1 FROM cms_contenidos WHERE clave = 'seo.keywords' LIMIT 1);
