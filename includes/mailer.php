<?php
/**
 * Envío de correo por SMTP (sin Composer).
 * Config: config/smtp.php (ver smtp.example.php).
 */
declare(strict_types=1);

/**
 * @return array{host:string,port:int,encryption:string,username:string,password:string,from_email:string,from_name:string}|null
 */
function mailer_config(): ?array
{
    $path = dirname(__DIR__) . '/config/smtp.php';
    if (!is_file($path)) {
        return null;
    }
    /** @var array $cfg */
    $cfg = require $path;
    if (!is_array($cfg) || empty($cfg['host']) || empty($cfg['username']) || empty($cfg['from_email'])) {
        return null;
    }
    return [
        'host' => (string) $cfg['host'],
        'port' => (int) ($cfg['port'] ?? 465),
        'encryption' => strtolower((string) ($cfg['encryption'] ?? 'ssl')),
        'username' => (string) $cfg['username'],
        'password' => (string) ($cfg['password'] ?? ''),
        'from_email' => (string) $cfg['from_email'],
        'from_name' => (string) ($cfg['from_name'] ?? 'MiDoc'),
    ];
}

/**
 * @return array{ok:bool,error?:string}
 */
function mailer_send(string $to, string $subject, string $htmlBody, string $textBody = ''): array
{
    $cfg = mailer_config();
    if ($cfg === null) {
        return ['ok' => false, 'error' => 'Falta configurar SMTP (config/smtp.php).'];
    }

    if (!filter_var($to, FILTER_VALIDATE_EMAIL)) {
        return ['ok' => false, 'error' => 'Correo destino inválido.'];
    }

    if ($textBody === '') {
        $textBody = trim(strip_tags(str_replace(['<br>', '<br/>', '<br />'], "\n", $htmlBody)));
    }

    $boundary = 'midoc_' . bin2hex(random_bytes(8));
    $fromName = mailer_encode_header($cfg['from_name']);
    $headers = [
        'Date: ' . date('r'),
        'From: ' . $fromName . ' <' . $cfg['from_email'] . '>',
        'Reply-To: ' . $cfg['from_email'],
        'MIME-Version: 1.0',
        'Content-Type: multipart/alternative; boundary="' . $boundary . '"',
        'X-Mailer: MiDoc-SMTP',
    ];

    $body = "--{$boundary}\r\n"
        . "Content-Type: text/plain; charset=UTF-8\r\n"
        . "Content-Transfer-Encoding: base64\r\n\r\n"
        . chunk_split(base64_encode($textBody))
        . "--{$boundary}\r\n"
        . "Content-Type: text/html; charset=UTF-8\r\n"
        . "Content-Transfer-Encoding: base64\r\n\r\n"
        . chunk_split(base64_encode($htmlBody))
        . "--{$boundary}--\r\n";

    $message = 'Subject: ' . mailer_encode_header($subject) . "\r\n"
        . implode("\r\n", $headers) . "\r\n\r\n"
        . $body;

    try {
        mailer_smtp_send($cfg, $to, $message);
        return ['ok' => true];
    } catch (Throwable $e) {
        return ['ok' => false, 'error' => 'No se pudo enviar el correo. Revisa SMTP.'];
    }
}

function mailer_encode_header(string $value): string
{
    if (preg_match('/^[\x20-\x7E]*$/', $value)) {
        return $value;
    }
    return '=?UTF-8?B?' . base64_encode($value) . '?=';
}

/**
 * @param array{host:string,port:int,encryption:string,username:string,password:string,from_email:string,from_name:string} $cfg
 */
function mailer_smtp_send(array $cfg, string $to, string $rawMessage): void
{
    $host = $cfg['host'];
    $port = $cfg['port'];
    $enc = $cfg['encryption'];

    $remote = ($enc === 'ssl' ? 'ssl://' : '') . $host . ':' . $port;
    $errno = 0;
    $errstr = '';
    $fp = @stream_socket_client($remote, $errno, $errstr, 20, STREAM_CLIENT_CONNECT);
    if (!$fp) {
        throw new RuntimeException('Conexión SMTP fallida: ' . $errstr);
    }
    stream_set_timeout($fp, 20);

    mailer_expect($fp, [220]);
    mailer_cmd($fp, 'EHLO midoc.local', [250]);

    if ($enc === 'tls') {
        mailer_cmd($fp, 'STARTTLS', [220]);
        $crypto = STREAM_CRYPTO_METHOD_TLS_CLIENT;
        if (defined('STREAM_CRYPTO_METHOD_TLSv1_2_CLIENT')) {
            $crypto = STREAM_CRYPTO_METHOD_TLSv1_2_CLIENT | STREAM_CRYPTO_METHOD_TLSv1_3_CLIENT;
        }
        if (!stream_socket_enable_crypto($fp, true, $crypto)) {
            throw new RuntimeException('STARTTLS falló.');
        }
        mailer_cmd($fp, 'EHLO midoc.local', [250]);
    }

    mailer_cmd($fp, 'AUTH LOGIN', [334]);
    mailer_cmd($fp, base64_encode($cfg['username']), [334]);
    mailer_cmd($fp, base64_encode($cfg['password']), [235]);

    mailer_cmd($fp, 'MAIL FROM:<' . $cfg['from_email'] . '>', [250]);
    mailer_cmd($fp, 'RCPT TO:<' . $to . '>', [250, 251]);
    mailer_cmd($fp, 'DATA', [354]);

    $safe = preg_replace('/^\./m', '..', $rawMessage) ?? $rawMessage;
    fwrite($fp, $safe . "\r\n.\r\n");
    mailer_expect($fp, [250]);
    mailer_cmd($fp, 'QUIT', [221]);
    fclose($fp);
}

/**
 * @param resource $fp
 * @param list<int> $ok
 */
function mailer_cmd($fp, string $line, array $ok): void
{
    fwrite($fp, $line . "\r\n");
    mailer_expect($fp, $ok);
}

/**
 * @param resource $fp
 * @param list<int> $ok
 */
function mailer_expect($fp, array $ok): void
{
    $response = '';
    while (($line = fgets($fp, 515)) !== false) {
        $response .= $line;
        if (isset($line[3]) && $line[3] === ' ') {
            break;
        }
    }
    $code = (int) substr($response, 0, 3);
    if (!in_array($code, $ok, true)) {
        throw new RuntimeException('SMTP error: ' . trim($response));
    }
}
