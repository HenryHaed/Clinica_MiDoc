-- Patch opcional si ya importaste schema.sql sin hero.fondo / imágenes seed.
-- Ejecutar en phpMyAdmin o mysql CLI. Seguro: INSERT IGNORE por clave única.

USE clinica_mi_doc;

INSERT IGNORE INTO cms_contenidos (seccion, clave, tipo, valor, etiqueta, updated_by) VALUES
('hero', 'hero.fondo', 'imagen',
 'https://lh3.googleusercontent.com/aida-public/AB6AXuAVMg_KK96LXgNH4IXadk-Z-dy-WtVQitA4H4Qp_-mo4_dVNOT45HLur0gXoM0LJ3i0IlI8YxHoOra7dZ_oH7fbSita9Jwr93lnVVqzBWLTv9jZHwqWt6fl8f1HryVxjrDRL3ThP1aFDmSWEXe7wEh5S7MvCqblaVB_h2QOU7R2tJmtGVktXPUqaH88-4dJa26ivxD3z0ajKAPddK0GR3SYD5lW4nA0TsTlTCQdpdhcsjDQpQJqMT6W',
 'Imagen de fondo del hero', 1),
('ubicacion', 'ubicacion.clinica', 'texto', 'Clínica Saint Jude', 'Nombre clínica (mapa)', 1),
('ubicacion', 'ubicacion.zona', 'texto', 'Zona San Clemente', 'Zona (mapa)', 1);

-- Si hero.imagen / mapa / foto doctor están vacíos, el panel Ajustes o cms_ensure_defaults los rellena.
