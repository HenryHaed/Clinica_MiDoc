<?php
/**
 * Config SMTP — copia a smtp.php y completa con tu cuenta de correo Hostinger.
 *
 * Hostinger típico:
 *   host: smtp.hostinger.com
 *   port: 465  (ssl)  ó  587 (tls)
 *   encryption: ssl   ó  tls
 *   username / password: el correo creado en hPanel → Correos
 */
return [
    'host' => 'smtp.hostinger.com',
    'port' => 465,
    'encryption' => 'ssl', // ssl | tls | none
    'username' => 'noreply@tudominio.com',
    'password' => 'PEGA_AQUI_LA_CLAVE_DEL_CORREO',
    'from_email' => 'noreply@tudominio.com',
    'from_name' => 'MiDoc',
];
