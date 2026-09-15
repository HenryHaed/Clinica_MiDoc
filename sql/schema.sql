-- =============================================================================
-- Clínica Mi Doc — esquema único MySQL / MariaDB (v4)
-- Archivo: sql/schema.sql
-- Incluye: tablas + roles + usuarios + sede + especialidades + servicios + CMS
--
-- LOCAL (XAMPP)
--   1) phpMyAdmin → Nueva → clinica_mi_doc (utf8mb4_unicode_ci)
--   2) Selecciona esa BD → Importar este archivo
--
-- HOSTINGER PREMIUM
--   1) hPanel → Bases de datos MySQL → crear BD y usuario (NO uses CREATE DATABASE aquí)
--   2) phpMyAdmin → selecciona TU base (ej. u123456789_midoc)
--   3) Importar este archivo
-- =============================================================================

SET NAMES utf8mb4;
SET time_zone = '+00:00';
SET FOREIGN_KEY_CHECKS = 0;

-- -----------------------------------------------------------------------------
-- Limpieza (idempotente para reimportar)
-- -----------------------------------------------------------------------------
DROP TABLE IF EXISTS audit_log;
DROP TABLE IF EXISTS password_resets;
DROP TABLE IF EXISTS visitas;
DROP TABLE IF EXISTS cms_contenidos;
DROP TABLE IF EXISTS documentos;
DROP TABLE IF EXISTS consultas;
DROP TABLE IF EXISTS antecedentes;
DROP TABLE IF EXISTS citas;
DROP TABLE IF EXISTS pacientes;
DROP TABLE IF EXISTS personal;
DROP TABLE IF EXISTS servicios;
DROP TABLE IF EXISTS especialidades;
DROP TABLE IF EXISTS sedes;
DROP TABLE IF EXISTS usuarios;
DROP TABLE IF EXISTS roles;

SET FOREIGN_KEY_CHECKS = 1;

-- =============================================================================
-- DDL
-- =============================================================================

-- -----------------------------------------------------------------------------
-- roles: catálogo de roles del sistema
-- -----------------------------------------------------------------------------
CREATE TABLE roles (
  id TINYINT UNSIGNED NOT NULL AUTO_INCREMENT,
  slug VARCHAR(40) NOT NULL,
  nombre VARCHAR(80) NOT NULL,
  descripcion VARCHAR(255) NULL,
  activo TINYINT(1) NOT NULL DEFAULT 1,
  created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (id),
  UNIQUE KEY uq_roles_slug (slug)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- -----------------------------------------------------------------------------
-- usuarios: cuentas con login
-- -----------------------------------------------------------------------------
CREATE TABLE usuarios (
  id INT UNSIGNED NOT NULL AUTO_INCREMENT,
  rol_id TINYINT UNSIGNED NOT NULL,
  email VARCHAR(190) NOT NULL,
  password_hash VARCHAR(255) NOT NULL,
  nombre VARCHAR(120) NOT NULL,
  apellido_paterno VARCHAR(80) NULL,
  apellido_materno VARCHAR(80) NULL,
  datos_extras TEXT NULL,
  activo TINYINT(1) NOT NULL DEFAULT 1,
  last_login_at DATETIME NULL,
  created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  deleted_at DATETIME NULL,
  PRIMARY KEY (id),
  UNIQUE KEY uq_usuarios_email (email),
  KEY idx_usuarios_rol (rol_id),
  KEY idx_usuarios_deleted (deleted_at),
  CONSTRAINT fk_usuarios_rol
    FOREIGN KEY (rol_id) REFERENCES roles (id)
    ON UPDATE CASCADE ON DELETE RESTRICT
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- -----------------------------------------------------------------------------
-- sedes: ubicaciones físicas de la clínica
-- -----------------------------------------------------------------------------
CREATE TABLE sedes (
  id SMALLINT UNSIGNED NOT NULL AUTO_INCREMENT,
  nombre VARCHAR(150) NOT NULL,
  direccion VARCHAR(255) NOT NULL,
  telefono VARCHAR(40) NULL,
  maps_url VARCHAR(500) NULL,
  horarios TEXT NULL,
  es_principal TINYINT(1) NOT NULL DEFAULT 0,
  activo TINYINT(1) NOT NULL DEFAULT 1,
  created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- -----------------------------------------------------------------------------
-- especialidades: catálogo médico
-- -----------------------------------------------------------------------------
CREATE TABLE especialidades (
  id SMALLINT UNSIGNED NOT NULL AUTO_INCREMENT,
  slug VARCHAR(80) NOT NULL,
  nombre VARCHAR(120) NOT NULL,
  descripcion VARCHAR(255) NULL,
  activo TINYINT(1) NOT NULL DEFAULT 1,
  created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (id),
  UNIQUE KEY uq_especialidades_slug (slug)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- -----------------------------------------------------------------------------
-- servicios: motivos de cita / cards de la landing
-- -----------------------------------------------------------------------------
CREATE TABLE servicios (
  id SMALLINT UNSIGNED NOT NULL AUTO_INCREMENT,
  slug VARCHAR(80) NOT NULL,
  titulo VARCHAR(150) NOT NULL,
  descripcion TEXT NOT NULL,
  detalle TEXT NULL,
  icono VARCHAR(80) NULL,
  imagen_url VARCHAR(500) NULL,
  categoria VARCHAR(40) NOT NULL DEFAULT 'adultos',
  orden INT NOT NULL DEFAULT 0,
  activo TINYINT(1) NOT NULL DEFAULT 1,
  destacado TINYINT(1) NOT NULL DEFAULT 0,
  created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  deleted_at DATETIME NULL,
  PRIMARY KEY (id),
  UNIQUE KEY uq_servicios_slug (slug),
  KEY idx_servicios_activo_orden (activo, orden),
  KEY idx_servicios_categoria (categoria),
  KEY idx_servicios_deleted (deleted_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- -----------------------------------------------------------------------------
-- personal: staff (doctor, enfermero, recepción, otro)
-- -----------------------------------------------------------------------------
CREATE TABLE personal (
  id INT UNSIGNED NOT NULL AUTO_INCREMENT,
  usuario_id INT UNSIGNED NULL,
  sede_id SMALLINT UNSIGNED NULL,
  especialidad_id SMALLINT UNSIGNED NULL,
  tipo ENUM('doctor','enfermero','recepcion','otro') NOT NULL,
  nombres VARCHAR(120) NOT NULL,
  apellidos VARCHAR(120) NOT NULL,
  matricula VARCHAR(80) NULL,
  bio TEXT NULL,
  foto_url VARCHAR(500) NULL,
  telefono VARCHAR(40) NULL,
  activo TINYINT(1) NOT NULL DEFAULT 1,
  created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  deleted_at DATETIME NULL,
  PRIMARY KEY (id),
  UNIQUE KEY uq_personal_usuario (usuario_id),
  KEY idx_personal_tipo (tipo),
  KEY idx_personal_sede (sede_id),
  KEY idx_personal_especialidad (especialidad_id),
  KEY idx_personal_deleted (deleted_at),
  CONSTRAINT fk_personal_usuario
    FOREIGN KEY (usuario_id) REFERENCES usuarios (id)
    ON UPDATE CASCADE ON DELETE SET NULL,
  CONSTRAINT fk_personal_sede
    FOREIGN KEY (sede_id) REFERENCES sedes (id)
    ON UPDATE CASCADE ON DELETE SET NULL,
  CONSTRAINT fk_personal_especialidad
    FOREIGN KEY (especialidad_id) REFERENCES especialidades (id)
    ON UPDATE CASCADE ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- -----------------------------------------------------------------------------
-- pacientes: ficha demográfica (puede existir sin usuario)
-- -----------------------------------------------------------------------------
CREATE TABLE pacientes (
  id INT UNSIGNED NOT NULL AUTO_INCREMENT,
  usuario_id INT UNSIGNED NULL,
  nombres VARCHAR(120) NOT NULL,
  apellidos VARCHAR(120) NOT NULL,
  tipo_documento ENUM('ci','pasaporte','otro') NOT NULL DEFAULT 'ci',
  documento VARCHAR(40) NULL,
  telefono VARCHAR(40) NULL,
  email VARCHAR(190) NULL,
  fecha_nacimiento DATE NULL,
  sexo ENUM('masculino','femenino','otro','no_especificado') NOT NULL DEFAULT 'no_especificado',
  grupo_sanguineo VARCHAR(5) NULL,
  notas_admin TEXT NULL,
  created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  deleted_at DATETIME NULL,
  PRIMARY KEY (id),
  UNIQUE KEY uq_pacientes_usuario (usuario_id),
  UNIQUE KEY uq_pacientes_documento (tipo_documento, documento),
  KEY idx_pacientes_telefono (telefono),
  KEY idx_pacientes_deleted (deleted_at),
  CONSTRAINT fk_pacientes_usuario
    FOREIGN KEY (usuario_id) REFERENCES usuarios (id)
    ON UPDATE CASCADE ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- -----------------------------------------------------------------------------
-- citas: solicitudes / agenda
-- -----------------------------------------------------------------------------
CREATE TABLE citas (
  id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  paciente_id INT UNSIGNED NULL,
  servicio_id SMALLINT UNSIGNED NULL,
  medico_id INT UNSIGNED NULL,
  sede_id SMALLINT UNSIGNED NULL,
  nombre_contacto VARCHAR(150) NOT NULL,
  telefono_contacto VARCHAR(40) NOT NULL,
  mensaje TEXT NULL,
  fecha_solicitada DATETIME NULL,
  fecha_confirmada DATETIME NULL,
  estado ENUM('pendiente','contactado','confirmada','cancelada','atendida') NOT NULL DEFAULT 'pendiente',
  origen ENUM('web_publica','admin','telefono','otro') NOT NULL DEFAULT 'web_publica',
  notas_internas TEXT NULL,
  ip VARCHAR(45) NULL,
  user_agent VARCHAR(255) NULL,
  leida_at DATETIME NULL,
  created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  deleted_at DATETIME NULL,
  PRIMARY KEY (id),
  KEY idx_citas_estado_created (estado, created_at),
  KEY idx_citas_paciente (paciente_id),
  KEY idx_citas_servicio (servicio_id),
  KEY idx_citas_medico (medico_id),
  KEY idx_citas_sede (sede_id),
  KEY idx_citas_deleted (deleted_at),
  CONSTRAINT fk_citas_paciente
    FOREIGN KEY (paciente_id) REFERENCES pacientes (id)
    ON UPDATE CASCADE ON DELETE SET NULL,
  CONSTRAINT fk_citas_servicio
    FOREIGN KEY (servicio_id) REFERENCES servicios (id)
    ON UPDATE CASCADE ON DELETE SET NULL,
  CONSTRAINT fk_citas_medico
    FOREIGN KEY (medico_id) REFERENCES personal (id)
    ON UPDATE CASCADE ON DELETE SET NULL,
  CONSTRAINT fk_citas_sede
    FOREIGN KEY (sede_id) REFERENCES sedes (id)
    ON UPDATE CASCADE ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- -----------------------------------------------------------------------------
-- antecedentes: historial permanente del paciente
-- -----------------------------------------------------------------------------
CREATE TABLE antecedentes (
  id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  paciente_id INT UNSIGNED NOT NULL,
  registrado_por INT UNSIGNED NULL,
  tipo ENUM('alergia','quirurgico','familiar','cronico','habito','otro') NOT NULL,
  descripcion TEXT NOT NULL,
  fecha_aproximada DATE NULL,
  activo TINYINT(1) NOT NULL DEFAULT 1,
  created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (id),
  KEY idx_antecedentes_paciente (paciente_id),
  KEY idx_antecedentes_tipo (tipo),
  CONSTRAINT fk_antecedentes_paciente
    FOREIGN KEY (paciente_id) REFERENCES pacientes (id)
    ON UPDATE CASCADE ON DELETE CASCADE,
  CONSTRAINT fk_antecedentes_usuario
    FOREIGN KEY (registrado_por) REFERENCES usuarios (id)
    ON UPDATE CASCADE ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- -----------------------------------------------------------------------------
-- consultas: encuentro clínico (núcleo del historial)
-- -----------------------------------------------------------------------------
CREATE TABLE consultas (
  id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  paciente_id INT UNSIGNED NOT NULL,
  cita_id BIGINT UNSIGNED NULL,
  profesional_id INT UNSIGNED NULL,
  fecha_hora DATETIME NOT NULL,
  motivo VARCHAR(255) NULL,
  anamnesis TEXT NULL,
  exploracion TEXT NULL,
  diagnostico TEXT NULL,
  diagnostico_cie VARCHAR(20) NULL,
  indicaciones TEXT NULL,
  plan_seguimiento TEXT NULL,
  signos_vitales JSON NULL COMMENT 'Ej: {"peso":70,"ta":"120/80","fc":72,"temp":36.5}',
  created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (id),
  UNIQUE KEY uq_consultas_cita (cita_id),
  KEY idx_consultas_paciente_fecha (paciente_id, fecha_hora),
  KEY idx_consultas_profesional (profesional_id),
  CONSTRAINT fk_consultas_paciente
    FOREIGN KEY (paciente_id) REFERENCES pacientes (id)
    ON UPDATE CASCADE ON DELETE CASCADE,
  CONSTRAINT fk_consultas_cita
    FOREIGN KEY (cita_id) REFERENCES citas (id)
    ON UPDATE CASCADE ON DELETE SET NULL,
  CONSTRAINT fk_consultas_profesional
    FOREIGN KEY (profesional_id) REFERENCES personal (id)
    ON UPDATE CASCADE ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- -----------------------------------------------------------------------------
-- documentos: archivos de paciente / consulta
-- -----------------------------------------------------------------------------
CREATE TABLE documentos (
  id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  paciente_id INT UNSIGNED NOT NULL,
  consulta_id BIGINT UNSIGNED NULL,
  subido_por INT UNSIGNED NULL,
  tipo ENUM('estudio','consentimiento','receta','imagen','otro') NOT NULL DEFAULT 'otro',
  nombre_original VARCHAR(255) NOT NULL,
  path_url VARCHAR(500) NOT NULL,
  mime VARCHAR(100) NULL,
  tamanio_bytes INT UNSIGNED NULL,
  created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (id),
  KEY idx_documentos_paciente (paciente_id),
  KEY idx_documentos_consulta (consulta_id),
  CONSTRAINT fk_documentos_paciente
    FOREIGN KEY (paciente_id) REFERENCES pacientes (id)
    ON UPDATE CASCADE ON DELETE CASCADE,
  CONSTRAINT fk_documentos_consulta
    FOREIGN KEY (consulta_id) REFERENCES consultas (id)
    ON UPDATE CASCADE ON DELETE SET NULL,
  CONSTRAINT fk_documentos_usuario
    FOREIGN KEY (subido_por) REFERENCES usuarios (id)
    ON UPDATE CASCADE ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- -----------------------------------------------------------------------------
-- cms_contenidos: textos/imágenes/config de la landing (clave-valor)
-- -----------------------------------------------------------------------------
CREATE TABLE cms_contenidos (
  id INT UNSIGNED NOT NULL AUTO_INCREMENT,
  seccion VARCHAR(60) NOT NULL,
  clave VARCHAR(100) NOT NULL,
  tipo ENUM('texto','html','imagen','url','numero') NOT NULL DEFAULT 'texto',
  valor TEXT NOT NULL,
  etiqueta VARCHAR(150) NOT NULL,
  updated_by INT UNSIGNED NULL,
  updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (id),
  UNIQUE KEY uq_cms_clave (clave),
  KEY idx_cms_seccion (seccion),
  CONSTRAINT fk_cms_usuario
    FOREIGN KEY (updated_by) REFERENCES usuarios (id)
    ON UPDATE CASCADE ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- -----------------------------------------------------------------------------
-- landing_videos: videos YouTube publicables desde Ajustes
-- -----------------------------------------------------------------------------
CREATE TABLE landing_videos (
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

-- -----------------------------------------------------------------------------
-- visitas: pageviews para el dashboard
-- -----------------------------------------------------------------------------
CREATE TABLE visitas (
  id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  fecha DATE NOT NULL,
  ip_hash VARCHAR(64) NOT NULL,
  pagina VARCHAR(255) NOT NULL DEFAULT '/',
  referrer VARCHAR(500) NULL,
  user_agent VARCHAR(255) NULL,
  created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (id),
  KEY idx_visitas_fecha (fecha),
  KEY idx_visitas_fecha_ip (fecha, ip_hash)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- -----------------------------------------------------------------------------
-- password_resets: códigos de recuperación por correo
-- -----------------------------------------------------------------------------
CREATE TABLE password_resets (
  id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  usuario_id INT UNSIGNED NOT NULL,
  email VARCHAR(190) NOT NULL,
  code_hash VARCHAR(255) NOT NULL,
  attempts TINYINT UNSIGNED NOT NULL DEFAULT 0,
  expires_at DATETIME NOT NULL,
  used_at DATETIME NULL,
  ip VARCHAR(45) NULL,
  created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (id),
  KEY idx_password_resets_email (email),
  KEY idx_password_resets_usuario (usuario_id),
  KEY idx_password_resets_expires (expires_at),
  CONSTRAINT fk_password_resets_usuario
    FOREIGN KEY (usuario_id) REFERENCES usuarios (id)
    ON UPDATE CASCADE ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- -----------------------------------------------------------------------------
-- audit_log: bitácora de acciones del panel
-- -----------------------------------------------------------------------------
CREATE TABLE audit_log (
  id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  usuario_id INT UNSIGNED NULL,
  accion VARCHAR(40) NOT NULL,
  tabla_afectada VARCHAR(60) NULL,
  registro_id VARCHAR(40) NULL,
  payload_json JSON NULL,
  ip VARCHAR(45) NULL,
  created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (id),
  KEY idx_audit_usuario (usuario_id),
  KEY idx_audit_created (created_at),
  CONSTRAINT fk_audit_usuario
    FOREIGN KEY (usuario_id) REFERENCES usuarios (id)
    ON UPDATE CASCADE ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =============================================================================
-- SEED
-- =============================================================================

-- Roles
INSERT INTO roles (id, slug, nombre, descripcion) VALUES
(1, 'super_admin', 'Super Administrador', 'Control total del sistema, usuarios y configuración crítica'),
(2, 'admin', 'Administrador', 'Dashboard, citas y edición de la landing'),
(3, 'doctor', 'Doctor', 'Atención de citas y registro de consultas'),
(4, 'enfermero', 'Enfermero/a', 'Apoyo clínico y signos vitales'),
(5, 'recepcion', 'Recepción', 'Gestión de citas y contacto con pacientes'),
(6, 'paciente', 'Paciente', 'Portal del paciente (uso futuro)');

-- Usuario bootstrap + cuentas de panel
-- Contraseña para todos: Password123$
INSERT INTO usuarios (id, rol_id, email, password_hash, nombre, activo) VALUES
(1, 1, 'midochernandigital@gmail.com', '$2y$10$8aR1KUc.wHLvs1oS.O5XKe/4mY3V4ikXpoq6a7CNfTQcRT9ZFrCnu', 'Super Admin MiDoc', 1),
(2, 2, 'gestor@clinicamidoc.com', '$2y$10$8aR1KUc.wHLvs1oS.O5XKe/4mY3V4ikXpoq6a7CNfTQcRT9ZFrCnu', 'Admin Gestión', 1),
(3, 3, 'doctor@clinicamidoc.com', '$2y$10$8aR1KUc.wHLvs1oS.O5XKe/4mY3V4ikXpoq6a7CNfTQcRT9ZFrCnu', 'Dr. Hernán Caihuara', 1),
(4, 4, 'enfermero@clinicamidoc.com', '$2y$10$8aR1KUc.wHLvs1oS.O5XKe/4mY3V4ikXpoq6a7CNfTQcRT9ZFrCnu', 'Enfermería MiDoc', 1),
(5, 5, 'recepcion@clinicamidoc.com', '$2y$10$8aR1KUc.wHLvs1oS.O5XKe/4mY3V4ikXpoq6a7CNfTQcRT9ZFrCnu', 'Recepción MiDoc', 1);

-- Sede principal
INSERT INTO sedes (id, nombre, direccion, telefono, maps_url, horarios, es_principal, activo) VALUES
(1, 'Clínica Saint Jude',
 'Piso 1, Plaza El Radialista N° 102, Zona San Clemente',
 '70688374',
 'https://maps.app.goo.gl/SeunT1EjTJ2AG4Nn9',
 'Lunes a Viernes: 8:00 AM - 6:00 PM\nSábados: Previa cita',
 1, 1);

-- Especialidades
INSERT INTO especialidades (id, slug, nombre, descripcion) VALUES
(1, 'cirugia-general', 'Cirugía General', 'Procedimientos quirúrgicos en adultos'),
(2, 'cirugia-pediatrica', 'Cirugía Pediátrica', 'Cirugía en pacientes pediátricos'),
(3, 'cirugia-laparoscopica', 'Cirugía Laparoscópica', 'Técnicas mínimamente invasivas'),
(4, 'cirugia-toracica', 'Cirugía Torácica', 'Afecciones del tórax');

-- Servicios (landing + formulario) — Adultos y Pediátrica
INSERT INTO servicios (slug, titulo, descripcion, icono, imagen_url, categoria, orden, activo, destacado) VALUES
('adulto-apendicitis', 'Apendicitis', 'Tratamiento quirúrgico de la apendicitis aguda en adultos.', 'gastroenterology', NULL, 'adultos', 10, 1, 0),
('adulto-colecistitis-litiasica', 'Colecistitis litiásica', 'Cirugía de vesícula por colecistitis litiásica.', 'gastroenterology', NULL, 'adultos', 20, 1, 0),
('adulto-hernias', 'Hernias', 'Reparación de hernias abdominales e inguinales en adultos.', 'personal_injury', NULL, 'adultos', 30, 1, 0),
('adulto-trauma-toracico', 'Trauma torácico', 'Atención quirúrgica de traumatismos del tórax.', 'pulmonology', NULL, 'adultos', 40, 1, 0),
('adulto-trauma-abdominal', 'Trauma abdominal', 'Manejo quirúrgico del trauma abdominal.', 'emergency', NULL, 'adultos', 50, 1, 0),
('adulto-cirugia-laparoscopica', 'Cirugía laparoscópica', 'Procedimientos mínimamente invasivos en adultos.', 'surgical', NULL, 'adultos', 60, 1, 0),
('adulto-ecografia-abdominal', 'Ecografía abdominal', 'Evaluación diagnóstica por ecografía abdominal.', 'radiology', NULL, 'adultos', 70, 1, 0),
('adulto-endoscopia-digestiva', 'Endoscopia digestiva', 'Estudios y procedimientos de endoscopia digestiva.', 'gastroenterology', NULL, 'adultos', 80, 1, 0),
('pediatrico-apendicitis', 'Apendicitis', 'Cirugía de apendicitis en pacientes pediátricos.', 'gastroenterology', NULL, 'pediatrica', 110, 1, 0),
('pediatrico-colecistitis-litiasica', 'Colecistitis litiásica', 'Manejo quirúrgico de colecistitis litiásica en niños.', 'gastroenterology', NULL, 'pediatrica', 120, 1, 0),
('pediatrico-hernias-labio-leporino', 'Hernias, labio leporino', 'Reparación de hernias y labio leporino en pediatría.', 'face', NULL, 'pediatrica', 130, 1, 0),
('pediatrico-criptorquidia-fimosis', 'Criptorquidia, fimosis', 'Cirugía pediátrica de criptorquidia y fimosis.', 'urology', NULL, 'pediatrica', 140, 1, 0),
('pediatrico-trauma-abdominal', 'Trauma abdominal', 'Atención del trauma abdominal en niños.', 'emergency', NULL, 'pediatrica', 150, 1, 0),
('pediatrico-cirugia-laparoscopica', 'Cirugía laparoscópica', 'Cirugía laparoscópica pediátrica.', 'surgical', NULL, 'pediatrica', 160, 1, 0),
('pediatrico-cirugia-neonatal', 'Cirugía neonatal', 'Procedimientos quirúrgicos en neonatos.', 'crib', NULL, 'pediatrica', 170, 1, 0),
('pediatrico-malformaciones', 'Malformaciones', 'Corrección quirúrgica de malformaciones congénitas.', 'genetics', NULL, 'pediatrica', 180, 1, 0),
('otro', 'Consulta General / Otro', 'Consulta general u otro motivo no listado en el catálogo.', 'stethoscope', NULL, 'adultos', 999, 1, 0);

-- Personal: Dr. Hernán vinculado al usuario doctor
INSERT INTO personal (
  id, usuario_id, sede_id, especialidad_id, tipo, nombres, apellidos, matricula, bio, foto_url, telefono, activo
) VALUES (
  1, 3, 1, 1, 'doctor',
  'Hernán', 'Caihuara Salas', NULL,
  'Cirujano General “Adultos” y Cirujano Pediatra. Comprometido con diagnósticos precisos, tratamientos efectivos y un trato humano para cada paciente y su familia.',
  '',
  '70688374',
  1
);

-- CMS / landing + config del sitio
INSERT INTO cms_contenidos (seccion, clave, tipo, valor, etiqueta, updated_by) VALUES
('config', 'config.sitio_nombre', 'texto', 'MiDoc', 'Nombre del sitio', 1),
('config', 'config.telefono', 'texto', '70688374', 'Teléfono visible (landing)', 1),
('config', 'config.whatsapp', 'texto', '59170688374', 'WhatsApp citas (código país + número)', 1),
('config', 'config.email', 'texto', 'midochernandigital@gmail.com', 'Email de contacto (footer / mailto)', 1),
('seo', 'seo.titulo', 'texto', 'MiDoc | Cirugía General y Pediátrica — Dr. Hernán Caihuara', 'Título SEO (Google)', 1),
('seo', 'seo.descripcion', 'texto',
 'Clínica MiDoc en Saint Jude. Dr. Hernán Caihuara Salas: cirugía general, pediátrica y laparoscópica. Atención profesional. Agenda tu cita al 70688374.',
 'Descripción SEO (Google)', 1),
('seo', 'seo.keywords', 'texto',
 'MiDoc, cirugía general, cirugía pediátrica, Dr Hernán Caihuara, Clínica Saint Jude, cirujano Bolivia, laparoscópica, hernia, apendicitis',
 'Palabras clave SEO', 1),
('hero', 'hero.badge', 'texto', 'Tu Salud es Primordial', 'Badge del hero', 1),
('hero', 'hero.titulo', 'texto', 'Atención médica de excelencia para ti y tu familia.', 'Título principal', 1),
('hero', 'hero.subtitulo', 'texto',
 'Dr. Hernán Caihuara Salas. Cirujano General "Adultos" y Cirujano Pediatra. Brindando diagnósticos precisos y tratamientos efectivos con tecnología de vanguardia.',
 'Subtítulo hero', 1),
('hero', 'hero.fondo', 'imagen',
 'https://lh3.googleusercontent.com/aida-public/AB6AXuAVMg_KK96LXgNH4IXadk-Z-dy-WtVQitA4H4Qp_-mo4_dVNOT45HLur0gXoM0LJ3i0IlI8YxHoOra7dZ_oH7fbSita9Jwr93lnVVqzBWLTv9jZHwqWt6fl8f1HryVxjrDRL3ThP1aFDmSWEXe7wEh5S7MvCqblaVB_h2QOU7R2tJmtGVktXPUqaH88-4dJa26ivxD3z0ajKAPddK0GR3SYD5lW4nA0TsTlTCQdpdhcsjDQpQJqMT6W',
 'Imagen de fondo del hero', 1),
('hero', 'hero.imagen', 'imagen',
 'https://lh3.googleusercontent.com/aida/AP1WRLstwO-aXpojVZEq3QNz-jTqzQ6_1hDoe4LK6awDBgE_PQPGsCT0Nh8DZ0CuxjvVrTDplSRoKpyYEAvKz68J9bFBRCwOzBDL7OKil2EgGUb-qnmORwIqYPjfAcRSsQddbzYeYe4CKwT9auvZIK9y9aaJqFsGo5_TJJ2h5sboOA7b_i5w5PZOja1TNPzXqn4LLN8BSYIu84QSqEOjbzGGphMrZzd5GqrfpAJZoWWjvd-DosGoQYTIh_KZmsc',
 'Imagen lateral del hero (URL o path)', 1),
('hero', 'hero.stat_experiencia', 'numero', '15', 'Años de experiencia', 1),
('hero', 'hero.stat_cirugias', 'numero', '1000', 'Cirugías exitosas', 1),
('especialista', 'especialista.nombre', 'texto', 'Dr. Hernán Caihuara Salas', 'Nombre del doctor', 1),
('especialista', 'especialista.rol', 'texto', 'Cirujano General “Adultos” y Cirujano Pediatra', 'Especialidad visible', 1),
('especialista', 'especialista.bio', 'texto',
 'Comprometido con diagnósticos precisos, tratamientos efectivos y un trato humano para cada paciente y su familia.',
 'Biografía corta', 1),
('especialista', 'especialista.foto', 'imagen',
 'https://lh3.googleusercontent.com/aida-public/AB6AXuDQ00TRNKSzW1IeH4fiOrmtUbcjbLCCGlPgfqZ_8yz3xGbzOPR_3PFPiqDHUnlcw0lT1aJHtm2aOeaVqnqMcuUWt6WGqWTzHUGEwXo9ANOEvrPZgJanwn-8iM8q_Ih2OaqvFbJXuZJg5nddpYjfMt3yjTMtfW6cyXJE4JCT9doBg-hvy-7UaDLCoPM9oQ2yg-shU3Ipn7V23KOOA03yR8lzdSodgbpiOWyvvV5CC5b0p4DwZ73pzDmB',
 'Foto del doctor', 1),
('servicios', 'servicios.titulo', 'texto', 'Atención en Cirugía General Adultos y Niños', 'Título sección servicios', 1),
('servicios', 'servicios.subtitulo', 'texto',
 'Procedimientos que ofrece el Dr. Hernán Caihuara Salas en Cirugía General Adultos y Cirugía Pediátrica.',
 'Subtítulo servicios', 1),
('servicios', 'servicios.titulo_adultos', 'texto', 'Cirugía General Adultos', 'Título bloque adultos', 1),
('servicios', 'servicios.titulo_pediatrica', 'texto', 'Cirugía Pediátrica', 'Título bloque pediátrica', 1),
('contacto', 'contacto.titulo', 'texto', 'Agenda tu Cita Médica', 'Título formulario', 1),
('contacto', 'contacto.subtitulo', 'texto',
 'Déjanos tus datos y nos pondremos en contacto contigo a la brevedad para confirmar tu horario de atención.',
 'Subtítulo formulario', 1),
('ubicacion', 'ubicacion.titulo', 'texto', 'Encuéntranos en Clínica Saint Jude', 'Título ubicación', 1),
('ubicacion', 'ubicacion.direccion', 'texto', 'Piso 1, Plaza El Radialista N° 102, Zona San Clemente', 'Dirección completa', 1),
('ubicacion', 'ubicacion.telefono', 'texto', '70688374', 'Teléfono visible', 1),
('ubicacion', 'ubicacion.horarios', 'texto', 'Lunes a Viernes: 8:00 AM - 6:00 PM\nSábados: Previa Cita', 'Horarios de atención', 1),
('ubicacion', 'ubicacion.maps_url', 'url',
 'https://maps.app.goo.gl/SeunT1EjTJ2AG4Nn9',
 'Link Google Maps (abrir en app/web)', 1),
('ubicacion', 'ubicacion.maps_embed', 'url',
 'https://www.google.com/maps/embed?pb=!1m14!1m8!1m3!1d1700.9216896407104!2d-65.7663181!3d-19.5776747!3m2!1i1024!2i768!4f13.1!3m3!1m2!1s0x93f94dc889649eaf%3A0x6e961c40ac838987!2sClinica%20Saint%20Jude!5e1!3m2!1ses!2sbo!4v1787687019456!5m2!1ses!2sbo',
 'Mapa embed (URL o pegar el iframe de Google Maps)', 1),
('ubicacion', 'ubicacion.mapa_imagen', 'imagen',
 'https://lh3.googleusercontent.com/aida-public/AB6AXuBg1ToGGMqGq3xdnCJhsXVMxOceBu_gwvZ84s5Tc04DinwW6azM-guuPPDFTkypubfv_YhfHHEfYwMbLy7RKa1Rx5uqFi6LR4wFAociBNow7DarvnWo9v5QjNXNMtNUNCMDVCg5ij69JJErG1-fWNti4UQSxqJDQ_EDmfkHMSVlPwz_6ARp7UQofCMZoD8q-SuHYhGsJJs9nk1-vxK2sAZLlBApRn2meaBy7Px02nxWvnqr5EP3GCl7',
 'Imagen de fondo (sección mapa)', 1),
('ubicacion', 'ubicacion.clinica', 'texto', 'Clínica Saint Jude', 'Nombre clínica', 1),
('ubicacion', 'ubicacion.zona', 'texto', 'Zona San Clemente', 'Zona', 1),
('footer', 'footer.texto', 'texto',
 'Atención médica de excelencia por el Dr. Hernán Caihuara Salas, brindando cuidado experto y compasivo para toda la familia.',
 'Texto del footer', 1),
('redes', 'redes.titulo', 'texto', 'Síguenos en nuestras redes sociales', 'Título sección redes', 1),
('redes', 'redes.subtitulo', 'texto', 'Contenido, tips de salud y novedades de MiDoc.', 'Subtítulo redes', 1),
('redes', 'redes.visible', 'numero', '1', 'Mostrar sección redes (1=sí, 0=no)', 1),
('redes', 'redes.facebook', 'url', 'https://www.facebook.com/share/1Yfdz5ojML/', 'Facebook (URL)', 1),
('redes', 'redes.youtube', 'url', 'https://www.youtube.com/@midochernan', 'YouTube (URL)', 1),
('redes', 'redes.tiktok', 'url', 'https://www.tiktok.com/@midochernan', 'TikTok (URL)', 1),
('videos', 'videos.titulo', 'texto', 'Videos y contenido', 'Título sección videos', 1),
('videos', 'videos.subtitulo', 'texto', 'Mira nuestras publicaciones y material educativo.', 'Subtítulo videos', 1),
('videos', 'videos.visible', 'numero', '1', 'Mostrar sección videos (1=sí, 0=no)', 1),
('theme', 'theme.cyan', 'color', '#003E67', 'Cian / azul principal', 1),
('theme', 'theme.yellow', 'color', '#F9D423', 'Amarillo (botones CTA)', 1),
('theme', 'theme.orange', 'color', '#FF9100', 'Naranja (acento)', 1),
('theme', 'theme.teal', 'color', '#169A8A', 'Teal / verde primario', 1),
('theme', 'theme.mint', 'color', '#3DE2AD', 'Menta', 1),
('theme', 'theme.sage', 'color', '#B2D9C2', 'Sage (bordes)', 1),
('theme', 'theme.mist', 'color', '#D9EEE1', 'Fondo (mist)', 1),
('theme', 'theme.text', 'color', '#14352F', 'Texto principal', 1),
('theme', 'theme.text_muted', 'color', '#3A5F56', 'Texto secundario', 1);

-- Cita de ejemplo (formulario público) — para probar el dashboard
INSERT INTO citas (
  servicio_id, medico_id, sede_id,
  nombre_contacto, telefono_contacto, mensaje,
  estado, origen, ip
) VALUES (
  3, 1, 1,
  'Juan Pérez', '70111222', 'Dolor en la zona inguinal, solicito evaluación.',
  'pendiente', 'web_publica', '127.0.0.1'
);

-- Visitas de ejemplo (últimos 3 días) — para gráficos del dashboard
INSERT INTO visitas (fecha, ip_hash, pagina, referrer, user_agent, created_at) VALUES
(CURDATE() - INTERVAL 2 DAY, SHA2('127.0.0.1-a', 256), '/', NULL, 'Mozilla/5.0 (seed)', NOW() - INTERVAL 2 DAY),
(CURDATE() - INTERVAL 2 DAY, SHA2('127.0.0.1-b', 256), '/', 'https://google.com', 'Mozilla/5.0 (seed)', NOW() - INTERVAL 2 DAY),
(CURDATE() - INTERVAL 1 DAY, SHA2('127.0.0.1-a', 256), '/', NULL, 'Mozilla/5.0 (seed)', NOW() - INTERVAL 1 DAY),
(CURDATE() - INTERVAL 1 DAY, SHA2('127.0.0.1-c', 256), '/index.php', NULL, 'Mozilla/5.0 (seed)', NOW() - INTERVAL 1 DAY),
(CURDATE() - INTERVAL 1 DAY, SHA2('127.0.0.1-d', 256), '/', NULL, 'Mozilla/5.0 (seed)', NOW() - INTERVAL 1 DAY),
(CURDATE(), SHA2('127.0.0.1-a', 256), '/', NULL, 'Mozilla/5.0 (seed)', NOW()),
(CURDATE(), SHA2('127.0.0.1-e', 256), '/', NULL, 'Mozilla/5.0 (seed)', NOW());

-- Audit de bootstrap
INSERT INTO audit_log (usuario_id, accion, tabla_afectada, registro_id, payload_json, ip) VALUES
(1, 'seed', 'usuarios', '1', JSON_OBJECT('evento', 'schema_import', 'version', 'v4'), '127.0.0.1');

-- =============================================================================
-- Cuentas de panel (todas: Password123$) — cámbialas en producción
--   admin@clinicamidoc.com      super_admin
--   gestor@clinicamidoc.com     admin
--   doctor@clinicamidoc.com     doctor
--   enfermero@clinicamidoc.com  enfermero
--   recepcion@clinicamidoc.com  recepcion
--
-- Regenerar hash:
--   php -r "echo password_hash('NUEVA_CLAVE', PASSWORD_DEFAULT);"
--   UPDATE usuarios SET password_hash='...' WHERE email='admin@clinicamidoc.com';
-- =============================================================================
