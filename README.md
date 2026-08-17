# Clínica Mi Doc

Sitio web y panel de la clínica del Dr. Hernán Caihuara Salas.  
PHP 8 + MySQL. Pensado para XAMPP (local) y Hostinger (producción).

## Arranque local

1. Copia el proyecto a `htdocs` y arranca Apache + MySQL.
2. En phpMyAdmin, crea la base `clinica_mi_doc` e importa `sql/schema.sql` (y `sql/seed_users.sql` si hace falta).
3. Abre `http://localhost/proyectos/Clinica-Mi-Doc/`

Cuentas de prueba: ver `sql/seed_users.sql`.

## Qué hay

| Ruta | Qué es |
|------|--------|
| `index.php` | Landing |
| `login.php` | Acceso al panel |
| `dashboard.php` | Panel según rol |
| `ajustes.php` | Textos, imágenes y especialidades |
| `config/db.php` | Conexión MySQL (local / Hostinger) |
| `includes/theme.php` | Paleta de colores |

Las imágenes subidas se guardan en `assets/uploads/Imagenes_Cargadas/`.

## Colores

Edita **`includes/theme.php`** (`theme_colors()`). El sitio toma esa paleta.

Si también compilas Tailwind en local (`npm run build`), replica los mismos hex en `tailwind.config.js`.

## Hostinger

1. Pon las credenciales reales en el bloque de producción de `config/db.php` (o usa `config/db.local.php` en local; no se sube a Git).
2. Sube el código PHP, `assets/`, `includes/`, `config/`, `sql/` y `api/`.
3. No subas `node_modules/`, `.env` ni archivos de uploads.

## GitHub

```bash
git init
git add .
git commit -m "Initial commit: Clínica Mi Doc"
git remote add origin https://github.com/USUARIO/Clinica-Mi-Doc.git
git push -u origin main
```
