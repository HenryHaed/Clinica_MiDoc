#!/usr/bin/env bash
# Genera midoc-hostinger.zip listo para extraer en public_html de Hostinger
# (sobrescribe archivos existentes; no borra uploads que ya estén en el servidor).
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

# PHP de la raíz
cp -a "$ROOT/index.php" "$ROOT/login.php" "$ROOT/dashboard.php" "$ROOT/ajustes.php" "$ROOT/recuperar.php" "$ROOT/sitemap.php" "$ROOT/robots.php" "$ROOT/robots.txt" "$STAGE/"
cp -a "$ROOT/.htaccess" "$ROOT/.user.ini" "$STAGE/"
cp -a "$ROOT/HOSTINGER.md" "$STAGE/" 2>/dev/null || true

copy_tree "$ROOT/api" "$STAGE/api"
copy_tree "$ROOT/includes" "$STAGE/includes"
copy_tree "$ROOT/sql" "$STAGE/sql"
copy_tree "$ROOT/storage" "$STAGE/storage"
copy_tree "$ROOT/config" "$STAGE/config"
copy_tree "$ROOT/assets" "$STAGE/assets"

# No llevar basura de desarrollo
rm -f "$STAGE/config/db.local.php"
rm -f "$STAGE/storage/"*.json 2>/dev/null || true
# Uploads vacíos en el ZIP (al extraer no borra imágenes ya subidas en Hostinger)
find "$STAGE/assets/uploads" -type f ! -name '.gitkeep' ! -name '.htaccess' -delete 2>/dev/null || true

# Instrucciones de actualización en masa
cat > "$STAGE/ACTUALIZAR-HOSTINGER.txt" << 'EOF'
ACTUALIZAR MiDoc EN HOSTINGER (sobrescritura en masa)
=====================================================

1) File Manager → entra a public_html
2) Sube este ZIP (midoc-hostinger.zip)
3) Clic derecho → Extract / Extraer
4) Cuando pregunte sobrescribir: SÍ / Overwrite / Replace
5) Borra el ZIP del servidor cuando termine

Qué incluye este paquete
------------------------
- index.php, login.php, recuperar.php, dashboard.php, ajustes.php
- sitemap.php, robots.php, robots.txt, .htaccess, .user.ini
- api/, includes/, assets/, config/, sql/, storage/
- config/db.hostinger.php y config/smtp.php (credenciales de producción)

Qué NO debes reimportar
-----------------------
- NO ejecutes otra vez todo sql/schema.sql (rompe/duplica datos).

Solo si tu BD es anterior a estos cambios, en phpMyAdmin (Importar)
ejecuta UNA VEZ cada patch que falte:
  sql/patch_password_resets.sql
  sql/patch_seo.sql
  sql/patch_ubicacion_maps.sql
  sql/patch_theme_colors.sql

Tras extraer
------------
- Comprueba: https://tudominio.com/
- API: https://tudominio.com/api/status.php
- Login: https://tudominio.com/login.php
- Ctrl+F5 en el navegador para ver CSS/JS nuevos

Notas
-----
- Las imágenes ya cargadas en assets/uploads suelen conservarse
  (este ZIP no trae esas fotos; solo la estructura vacía).
- Si al extraer aparece una carpeta extra, mueve el contenido a public_html.
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
echo "Contenido principal:"
(
  cd "$STAGE"
  find . -maxdepth 2 -type f | sort | head -n 60
)
echo ""
echo "En Hostinger: public_html → Upload ZIP → Extract (Overwrite) → borrar ZIP."
