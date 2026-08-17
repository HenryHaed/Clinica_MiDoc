<?php
/**
 * Subida segura de imágenes.
 * Regla general: todo archivo subido se guarda en assets/uploads/Imagenes_Cargadas/
 */
declare(strict_types=1);

const UPLOAD_PUBLIC_DIR = 'assets/uploads/Imagenes_Cargadas';

/**
 * @return array{ok:bool,path?:string,error?:string}
 */
function upload_image(array $file, string $prefix = 'img'): array
{
    if (($file['error'] ?? UPLOAD_ERR_NO_FILE) === UPLOAD_ERR_NO_FILE) {
        return ['ok' => false, 'error' => 'No se envió ningún archivo.'];
    }
    if (($file['error'] ?? UPLOAD_ERR_OK) !== UPLOAD_ERR_OK) {
        return ['ok' => false, 'error' => 'Error al subir el archivo.'];
    }

    $maxBytes = 5 * 1024 * 1024;
    if (($file['size'] ?? 0) > $maxBytes) {
        return ['ok' => false, 'error' => 'La imagen supera 5 MB.'];
    }

    $tmp = (string) ($file['tmp_name'] ?? '');
    if ($tmp === '' || !is_uploaded_file($tmp)) {
        return ['ok' => false, 'error' => 'Archivo temporal inválido.'];
    }

    $finfo = new finfo(FILEINFO_MIME_TYPE);
    $mime = $finfo->file($tmp) ?: '';
    $map = [
        'image/jpeg' => 'jpg',
        'image/png' => 'png',
        'image/webp' => 'webp',
        'image/gif' => 'gif',
    ];
    if (!isset($map[$mime])) {
        return ['ok' => false, 'error' => 'Formato no permitido. Usa JPG, PNG, WEBP o GIF.'];
    }

    $prefix = preg_replace('/[^a-z0-9_-]/i', '', $prefix) ?: 'img';
    $dir = dirname(__DIR__) . '/' . UPLOAD_PUBLIC_DIR;
    if (!is_dir($dir) && !mkdir($dir, 0755, true) && !is_dir($dir)) {
        return ['ok' => false, 'error' => 'No se pudo crear la carpeta de uploads.'];
    }

    $name = $prefix . '-' . date('Ymd-His') . '-' . bin2hex(random_bytes(4)) . '.' . $map[$mime];
    $dest = $dir . '/' . $name;
    if (!move_uploaded_file($tmp, $dest)) {
        return ['ok' => false, 'error' => 'No se pudo guardar la imagen.'];
    }

    return ['ok' => true, 'path' => UPLOAD_PUBLIC_DIR . '/' . $name];
}
