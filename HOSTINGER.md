# Subir a Hostinger Premium (PHP)

El plan Premium de Hostinger es hosting PHP + MySQL. Este proyecto no usa Node, Python ni Laravel en el servidor.

## 1. PHP

hPanel → **Sitios web** → **Configuración avanzada** → **Configuración PHP**

- Versión: **PHP 8.2** (o 8.1 / 8.3). No uses 7.x.
- Extensiones: `pdo_mysql`, `mbstring`, `fileinfo`, `json`, `session` (normalmente ya vienen).

El archivo `.user.ini` de la raíz ya pide 8 MB de subida de imágenes.

## 2. Base de datos

hPanel → **Bases de datos** → **Bases de datos MySQL**

1. Crea una base, por ejemplo `midoc` (Hostinger le pondrá prefijo: `u123456789_midoc`).
2. Crea un usuario y una contraseña fuerte.
3. Asigna ese usuario a esa base con **todos los privilegios**.
4. Anota: **nombre de la BD**, **usuario**, **contraseña**. El host es casi siempre `localhost`.

### Importar tablas

1. hPanel → **phpMyAdmin** → entra a **tu** base (clic en el nombre a la izquierda).
2. Pestaña **Importar** → elige `sql/schema.sql` → Ejecutar.

Ese archivo ya trae tablas, textos de la landing, especialidades y usuarios. **No** ejecutes `CREATE DATABASE` (Hostinger no lo permite; por eso el SQL ya no lo incluye).

Cuentas iniciales (cámbialas después):

| Email | Rol | Clave |
|-------|-----|--------|
| admin@clinicamidoc.com | Super admin | `Password123$` |
| gestor@clinicamidoc.com | Admin | `Password123$` |
| doctor@clinicamidoc.com | Doctor | `Password123$` |
| enfermero@clinicamidoc.com | Enfermero | `Password123$` |
| recepcion@clinicamidoc.com | Recepción | `Password123$` |

## 3. Credenciales PHP

En tu PC:

```bash
copy config\db.hostinger.example.php config\db.hostinger.php
```

Edita `config/db.hostinger.php` con nombre, usuario y contraseña de hPanel.

Ese archivo **sí se sube** a Hostinger y **no** va a Git.

## 3b. Correo SMTP (recuperar contraseña)

1. hPanel → **Correos** → crea un buzón (ej. `noreply@tudominio.com`).
2. En tu PC:

```bash
copy config\smtp.example.php config\smtp.php
```

3. Completa host `smtp.hostinger.com`, puerto `465` + `ssl` (o `587` + `tls`), usuario y contraseña del correo.
4. Sube `config/smtp.php` junto con el sitio.

Si la BD ya existía, importa también `sql/patch_password_resets.sql`.

## 4. Subir archivos (ZIP)

En este proyecto ya está **`midoc-hostinger.zip`** (se regenera con `bash pack-hostinger.sh`). Incluye el PHP, assets, `.htaccess`, `.user.ini` y `config/db.hostinger.php`.

1. hPanel → **File Manager** → carpeta **`public_html`** (esa es la web; a veces el administrador de archivos se ve como Files / srvfiles).
2. Sube **un solo archivo**: `midoc-hostinger.zip`.
3. Clic derecho → **Extract**.
4. Confirma que `index.php` quedó **dentro** de `public_html`, no en `public_html/Clinica-Mi-Doc/`.
5. Borra el ZIP del servidor.

Si extraes y ves una carpeta extra, mueve el contenido a `public_html`.

No subas `node_modules/`, `src/` ni `.git`.

Permisos: carpetas `755`, archivos `644`.  
`assets/uploads/Imagenes_Cargadas` y `storage` deben poder escribirse.

## 5. SSL y dominio

hPanel → **SSL** → activar certificado (Let's Encrypt).  
El `.htaccess` redirige HTTP → HTTPS.

Si el sitio queda en una subcarpeta (no en la raíz del dominio), avísame: hay que ajustar la cookie de sesión.

## 6. Comprobar

- `https://tudominio.com/` — landing
- `https://tudominio.com/api/status.php` — debe decir que la BD está OK
- `https://tudominio.com/login.php` — entra con `admin@clinicamidoc.com`
- Agenda una cita de prueba en la landing

Luego cambia las claves de los usuarios desde phpMyAdmin (`password_hash` bcrypt) o regenera el hash:

```bash
php -r "echo password_hash('TU_CLAVE_NUEVA', PASSWORD_DEFAULT);"
```
