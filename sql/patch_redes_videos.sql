-- Redes sociales + sección videos (BD ya existentes)
-- Ejecutar UNA VEZ en phpMyAdmin.

CREATE TABLE IF NOT EXISTS landing_videos (
  id INT UNSIGNED NOT NULL AUTO_INCREMENT,
  titulo VARCHAR(180) NOT NULL,
  descripcion TEXT NULL,
  youtube_url VARCHAR(500) NOT NULL,
  youtube_id VARCHAR(20) NOT NULL,
  orden INT NOT NULL DEFAULT 0,
  activo TINYINT(1) NOT NULL DEFAULT 1,
  created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (id),
  KEY idx_landing_videos_activo_orden (activo, orden, id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT INTO cms_contenidos (seccion, clave, tipo, valor, etiqueta, updated_by)
SELECT 'redes', 'redes.titulo', 'texto', 'Síguenos en nuestras redes sociales', 'Título sección redes', NULL
WHERE NOT EXISTS (SELECT 1 FROM cms_contenidos WHERE clave = 'redes.titulo' LIMIT 1);

INSERT INTO cms_contenidos (seccion, clave, tipo, valor, etiqueta, updated_by)
SELECT 'redes', 'redes.subtitulo', 'texto', 'Contenido, tips de salud y novedades de MiDoc.', 'Subtítulo redes', NULL
WHERE NOT EXISTS (SELECT 1 FROM cms_contenidos WHERE clave = 'redes.subtitulo' LIMIT 1);

INSERT INTO cms_contenidos (seccion, clave, tipo, valor, etiqueta, updated_by)
SELECT 'redes', 'redes.visible', 'numero', '1', 'Mostrar sección redes (1=sí, 0=no)', NULL
WHERE NOT EXISTS (SELECT 1 FROM cms_contenidos WHERE clave = 'redes.visible' LIMIT 1);

INSERT INTO cms_contenidos (seccion, clave, tipo, valor, etiqueta, updated_by)
SELECT 'redes', 'redes.facebook', 'url', 'https://www.facebook.com/share/1Yfdz5ojML/', 'Facebook (URL)', NULL
WHERE NOT EXISTS (SELECT 1 FROM cms_contenidos WHERE clave = 'redes.facebook' LIMIT 1);

INSERT INTO cms_contenidos (seccion, clave, tipo, valor, etiqueta, updated_by)
SELECT 'redes', 'redes.youtube', 'url', 'https://www.youtube.com/@midochernan', 'YouTube (URL)', NULL
WHERE NOT EXISTS (SELECT 1 FROM cms_contenidos WHERE clave = 'redes.youtube' LIMIT 1);

INSERT INTO cms_contenidos (seccion, clave, tipo, valor, etiqueta, updated_by)
SELECT 'redes', 'redes.tiktok', 'url', 'https://www.tiktok.com/@midochernan', 'TikTok (URL)', NULL
WHERE NOT EXISTS (SELECT 1 FROM cms_contenidos WHERE clave = 'redes.tiktok' LIMIT 1);

INSERT INTO cms_contenidos (seccion, clave, tipo, valor, etiqueta, updated_by)
SELECT 'videos', 'videos.titulo', 'texto', 'Videos y contenido', 'Título sección videos', NULL
WHERE NOT EXISTS (SELECT 1 FROM cms_contenidos WHERE clave = 'videos.titulo' LIMIT 1);

INSERT INTO cms_contenidos (seccion, clave, tipo, valor, etiqueta, updated_by)
SELECT 'videos', 'videos.subtitulo', 'texto', 'Mira nuestras publicaciones y material educativo.', 'Subtítulo videos', NULL
WHERE NOT EXISTS (SELECT 1 FROM cms_contenidos WHERE clave = 'videos.subtitulo' LIMIT 1);

INSERT INTO cms_contenidos (seccion, clave, tipo, valor, etiqueta, updated_by)
SELECT 'videos', 'videos.visible', 'numero', '1', 'Mostrar sección videos (1=sí, 0=no)', NULL
WHERE NOT EXISTS (SELECT 1 FROM cms_contenidos WHERE clave = 'videos.visible' LIMIT 1);
