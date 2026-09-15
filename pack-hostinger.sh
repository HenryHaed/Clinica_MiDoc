#!/usr/bin/env bash
# Genera midoc-hostinger.zip listo para extraer en public_html de Hostinger
# Paquete COMPLETO (sin node_modules / sin .git / sin src de Tailwind).
set -euo pipefail

ROOT="$(cd "$(dirname "$0")" && pwd)"
STAGE="$ROOT/dist-hostinger"
ZIP="$ROOT/midoc-hostinger.zip"

if [[ ! -f "$ROOT/config/db.hostinger.php" ]]; then
  echo "Falta config/db.hostinger.php. Cópialo desde db.hostinger.example.php y completa los datos."
  exit 1
fi

rm -rf "$STAGE"
mkdir -p "$STAGE"

copy_tree() {
  local src="$1"
  local dest="$2"
  mkdir -p "$dest"
  cp -a "$src/." "$dest/"
}

# PHP de la raíz (sitio + panel)
cp -a \
  "$ROOT/index.php" \
  "$ROOT/login.php" \
  "$ROOT/dashboard.php" \
  "$ROOT/ajustes.php" \
  "$ROOT/usuarios.php" \
  "$ROOT/recuperar.php" \
  "$ROOT/sitemap.php" \
  "$ROOT/robots.php" \
  "$ROOT/robots.txt" \
  "$STAGE/"
cp -a "$ROOT/.htaccess" "$ROOT/.user.ini" "$STAGE/"
cp -a "$ROOT/HOSTINGER.md" "$STAGE/" 2>/dev/null || true

copy_tree "$ROOT/api" "$STAGE/api"
copy_tree "$ROOT/includes" "$STAGE/includes"
copy_tree "$ROOT/sql" "$STAGE/sql"
copy_tree "$ROOT/storage" "$STAGE/storage"
copy_tree "$ROOT/config" "$STAGE/config"
copy_tree "$ROOT/assets" "$STAGE/assets"

# No llevar basura / desarrollo
rm -f "$STAGE/config/db.local.php" 2>/dev/null || true
rm -f "$STAGE/storage/"*.json 2>/dev/null || true
# Quitar ejemplos de credenciales del ZIP (ya van db.hostinger.php y smtp.php)
# (se mantienen .example por si hace falta recrear; no afectan)

# Instrucciones
cat > "$STAGE/LEEME-INSTALAR.txt" << 'EOF'
MiDoc — PAQUETE COMPLETO PARA HOSTINGER
=======================================

Este ZIP es el sitio completo (sin node_modules, sin .git, sin src).

ANTES DE BORRAR EN EL SERVIDOR
------------------------------
1) Si tienes imágenes ya subidas en el hosting, descarga de respaldo:
   public_html/assets/uploads/
2) Anota tu config/db.hostinger.php y config/smtp.php por si acaso
   (este ZIP ya trae los de tu PC local de producción).

INSTALACIÓN LIMPIA (reemplazo total)
------------------------------------
1) File Manager → public_html
2) Borra el contenido anterior de public_html (o muévelo a una carpeta _old)
3) Sube midoc-hostinger.zip
4) Clic derecho → Extract / Extraer
5) Confirma que index.php quedó DENTRO de public_html
   (no en public_html/Clinica-Mi-Doc/)
6) Borra el ZIP del servidor

BASE DE DATOS
-------------
- Si la BD YA existe y funciona: NO reimportes sql/schema.sql.
  Solo aplica en phpMyAdmin los patch_*.sql que aún no hayas corrido
  (si sale "Duplicate column", ignóralo):
    sql/patch_whatsapp.sql
    sql/patch_redes_videos.sql
    sql/patch_servicios_adultos_pediatria.sql
    sql/patch_servicios_modal.sql
    sql/patch_usuarios_perfil.sql
    sql/patch_seo.sql
    sql/patch_password_resets.sql
    sql/patch_ubicacion_maps.sql
    sql/patch_theme_colors.sql

- Si la BD es NUEVA / vacía: Importar UNA VEZ sql/schema.sql
  (ya incluye estructura actualizada). Luego no hace falta reimportarlo.

QUÉ INCLUYE
-----------
- index.php, login.php, recuperar.php, dashboard.php, ajustes.php, usuarios.php
- sitemap.php, robots.php, robots.txt, .htaccess, .user.ini
- api/, includes/, assets/, config/, sql/, storage/
- config/db.hostinger.php y config/smtp.php

QUÉ NO INCLUYE (a propósito)
----------------------------
- node_modules/, package.json, src/, .git/, dist antiguos
- Documentación de desarrollo (plan.md, etc.)

COMPROBAR
---------
- https://tudominio.com/
- https://tudominio.com/api/status.php
- https://tudominio.com/login.php
- Ctrl+F5 para CSS/JS nuevos

Permisos sugeridos: carpetas 755, archivos 644.
assets/uploads y storage deben poder escribirse.
EOF

rm -f "$ZIP"
export STAGE_DIR="$STAGE"
export ZIP_PATH="$ZIP"

PHP_BIN=""
if [[ -x "/c/xampp/php/php.exe" ]]; then
  PHP_BIN="/c/xampp/php/php.exe"
elif command -v php >/dev/null 2>&1; then
  PHP_BIN="$(command -v php)"
fi
if [[ -z "$PHP_BIN" ]]; then
  echo "No se encontró PHP para crear el ZIP."
  exit 1
fi

"$PHP_BIN" -r '
$root = getenv("STAGE_DIR");
$zipPath = getenv("ZIP_PATH");
if (!$root || !$zipPath || !is_dir($root)) { fwrite(STDERR, "STAGE/ZIP inválidos\n"); exit(1); }
@unlink($zipPath);
$zip = new ZipArchive();
if ($zip->open($zipPath, ZipArchive::CREATE) !== true) { fwrite(STDERR, "No se pudo crear zip\n"); exit(1); }
$root = realpath($root);
$it = new RecursiveIteratorIterator(
  new RecursiveDirectoryIterator($root, FilesystemIterator::SKIP_DOTS),
  RecursiveIteratorIterator::SELF_FIRST
);
foreach ($it as $file) {
  $full = $file->getPathname();
  $rel = str_replace("\\", "/", substr($full, strlen($root) + 1));
  if ($file->isDir()) {
    $zip->addEmptyDir($rel);
  } else {
    $zip->addFile($full, $rel);
  }
}
$zip->close();
echo "ZIP OK\n";
'

echo ""
echo "Listo: $ZIP"
ls -lh "$ZIP"
echo ""
echo "Archivos en el paquete (máx. profundidad 2):"
(
  cd "$STAGE"
  find . -maxdepth 2 \( -type f -o -type d \) | sort | head -n 80
)
echo ""
echo "En Hostinger: vacía public_html → Upload ZIP → Extract → borrar ZIP."
