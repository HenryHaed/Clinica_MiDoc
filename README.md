# Clínica Mi Doc

Sitio y panel del Dr. Hernán Caihuara Salas. PHP 8 + MySQL (XAMPP local / Hostinger Premium).

## Local

1. Apache + MySQL en XAMPP.
2. phpMyAdmin: crea `clinica_mi_doc` (utf8mb4) e importa **`sql/schema.sql`**.
3. Abre `http://localhost/proyectos/Clinica-Mi-Doc/`

Cuentas de prueba: están en el mismo `schema.sql` (clave `Password123$`).

## Hostinger

Sigue **`HOSTINGER.md`**: PHP 8.2, crear BD en hPanel, importar `sql/schema.sql`, copiar `config/db.hostinger.example.php` → `db.hostinger.php`.

## Archivos clave

| Ruta | Uso |
|------|-----|
| `index.php` | Landing |
| `login.php` / `dashboard.php` / `ajustes.php` | Panel |
| `config/db.php` | Conexión (local o Hostinger) |
| `includes/theme.php` | Colores |
| `sql/schema.sql` | Toda la base de datos |
