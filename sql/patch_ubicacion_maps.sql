-- Patch: link Google Maps oficial + campo embed editable (BD ya existentes)
-- Ejecutar una sola vez en phpMyAdmin si la BD ya estaba creada.

UPDATE cms_contenidos
SET valor = 'https://maps.app.goo.gl/SeunT1EjTJ2AG4Nn9',
    etiqueta = 'Link Google Maps (abrir en app/web)',
    updated_at = CURRENT_TIMESTAMP
WHERE clave = 'ubicacion.maps_url';

INSERT INTO cms_contenidos (seccion, clave, tipo, valor, etiqueta, updated_by)
SELECT 'ubicacion', 'ubicacion.maps_embed', 'url',
       'https://www.google.com/maps?q=Plaza+El+Radialista+102,+Zona+San+Clemente&output=embed',
       'URL embed del mapa (iframe)', NULL
WHERE NOT EXISTS (
  SELECT 1 FROM cms_contenidos WHERE clave = 'ubicacion.maps_embed' LIMIT 1
);

UPDATE sedes
SET maps_url = 'https://maps.app.goo.gl/SeunT1EjTJ2AG4Nn9'
WHERE es_principal = 1;
