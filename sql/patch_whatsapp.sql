-- WhatsApp para citas (BD ya existentes). Ejecutar UNA VEZ.

INSERT INTO cms_contenidos (seccion, clave, tipo, valor, etiqueta, updated_by)
SELECT 'config', 'config.whatsapp', 'texto', '59170688374', 'WhatsApp citas (código país + número)', NULL
WHERE NOT EXISTS (SELECT 1 FROM cms_contenidos WHERE clave = 'config.whatsapp' LIMIT 1);

UPDATE cms_contenidos
SET etiqueta = 'Teléfono visible (landing)',
    updated_at = CURRENT_TIMESTAMP
WHERE clave = 'config.telefono';
