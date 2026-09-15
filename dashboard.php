<?php
/**
 * Dashboard — métricas y actividad reales desde la BD (sin mocks).
 */
declare(strict_types=1);

require_once __DIR__ . '/includes/auth.php';
require_once __DIR__ . '/includes/theme.php';
require_once __DIR__ . '/includes/cms.php';

auth_start();

if (isset($_GET['logout'])) {
    auth_logout();
    header('Location: login.php');
    exit;
}

auth_require(...AUTH_PANEL_ROLES);

$user = auth_user();
assert($user !== null);

$nombre = $user['nombre'];
$rolSlug = $user['rol_slug'];
$rolNombre = $user['rol_nombre'];
$email = $user['email'];

$navActive = 'dashboard';

$roleCopy = [
    'super_admin' => [
        'subtitle' => 'Control total del sistema',
        'welcome' => 'Panel de Super Administración',
        'focus' => 'Revisa solicitudes web, servicios publicados y la configuración del sitio en Ajustes.',
    ],
    'admin' => [
        'subtitle' => 'Gestión operativa',
        'welcome' => 'Panel de Administración',
        'focus' => 'Consulta las solicitudes de cita y administra el contenido de la landing desde Ajustes.',
    ],
    'doctor' => [
        'subtitle' => 'Área clínica',
        'welcome' => 'Panel del Doctor',
        'focus' => 'Resumen de solicitudes web registradas. Los módulos clínicos se activarán más adelante.',
    ],
    'enfermero' => [
        'subtitle' => 'Apoyo clínico',
        'welcome' => 'Panel de Enfermería',
        'focus' => 'Resumen operativo actual. Los módulos clínicos se activarán más adelante.',
    ],
    'recepcion' => [
        'subtitle' => 'Atención al paciente',
        'welcome' => 'Panel de Recepción',
        'focus' => 'Revisa las solicitudes de cita que llegan desde la web.',
    ],
];

$copy = $roleCopy[$rolSlug] ?? $roleCopy['admin'];

/** Contadores seguros (0 si la tabla no existe o falla) */
$dashCount = static function (string $sql): int {
    try {
        $n = db()->query($sql)->fetchColumn();
        return (int) $n;
    } catch (Throwable $e) {
        return 0;
    }
};

$citasTotal = $dashCount("SELECT COUNT(*) FROM citas WHERE deleted_at IS NULL");
$citasPendientes = $dashCount("SELECT COUNT(*) FROM citas WHERE deleted_at IS NULL AND estado = 'pendiente'");
$citasHoy = $dashCount(
    "SELECT COUNT(*) FROM citas WHERE deleted_at IS NULL AND DATE(created_at) = CURDATE()"
);
$pacientesTotal = $dashCount('SELECT COUNT(*) FROM pacientes');
$serviciosActivos = $dashCount(
    "SELECT COUNT(*) FROM servicios WHERE deleted_at IS NULL AND activo = 1 AND slug <> 'otro'"
);
$videosActivos = $dashCount(
    "SELECT COUNT(*) FROM landing_videos WHERE deleted_at IS NULL AND activo = 1"
);
$usuariosActivos = $dashCount(
    "SELECT COUNT(*) FROM usuarios WHERE deleted_at IS NULL AND activo = 1"
);

$canAjustes = auth_can('cms') || auth_can('config');

$stats = [];
if ($canAjustes) {
    $stats = [
        [
            'label' => 'Solicitudes web',
            'value' => (string) $citasTotal,
            'icon' => 'inbox',
            'tone' => 'cyan',
            'hint' => 'Total registradas',
        ],
        [
            'label' => 'Pendientes',
            'value' => (string) $citasPendientes,
            'icon' => 'pending_actions',
            'tone' => 'orange',
            'hint' => 'Por contactar',
        ],
        [
            'label' => 'Hoy',
            'value' => (string) $citasHoy,
            'icon' => 'today',
            'tone' => 'teal',
            'hint' => 'Recibidas hoy',
        ],
        [
            'label' => 'Servicios activos',
            'value' => (string) $serviciosActivos,
            'icon' => 'medical_services',
            'tone' => 'mint',
            'hint' => 'Publicados en landing',
        ],
    ];
} else {
    $stats = [
        [
            'label' => 'Solicitudes web',
            'value' => (string) $citasTotal,
            'icon' => 'inbox',
            'tone' => 'cyan',
            'hint' => 'Total registradas',
        ],
        [
            'label' => 'Pendientes',
            'value' => (string) $citasPendientes,
            'icon' => 'pending_actions',
            'tone' => 'orange',
            'hint' => 'Por contactar',
        ],
        [
            'label' => 'Hoy',
            'value' => (string) $citasHoy,
            'icon' => 'today',
            'tone' => 'teal',
            'hint' => 'Recibidas hoy',
        ],
        [
            'label' => 'Pacientes',
            'value' => (string) $pacientesTotal,
            'icon' => 'group',
            'tone' => 'mint',
            'hint' => 'En el sistema',
        ],
    ];
}

/** @var list<array<string,mixed>> $recentCitas */
$recentCitas = [];
try {
    $recentCitas = db()->query(
        "SELECT id, nombre_contacto, telefono_contacto, mensaje, estado, origen, created_at
         FROM citas
         WHERE deleted_at IS NULL
         ORDER BY created_at DESC, id DESC
         LIMIT 8"
    )->fetchAll(PDO::FETCH_ASSOC);
} catch (Throwable $e) {
    $recentCitas = [];
}

$estadoLabel = static function (string $estado): string {
    return match ($estado) {
        'pendiente' => 'Pendiente',
        'contactado' => 'Contactado',
        'confirmada' => 'Confirmada',
        'cancelada' => 'Cancelada',
        'atendida' => 'Atendida',
        default => $estado,
    };
};

$estadoTone = static function (string $estado): string {
    return match ($estado) {
        'pendiente' => 'bg-brand-yellow/40 text-on-secondary-fixed',
        'contactado' => 'bg-light-cyan/20 text-cyan-deep',
        'confirmada' => 'bg-mint/50 text-teal',
        'cancelada' => 'bg-error-container text-on-error-container',
        'atendida' => 'bg-surface-container text-on-surface-variant',
        default => 'bg-surface-container text-on-surface-variant',
    };
};

$relativeWhen = static function (?string $dt): string {
    if ($dt === null || $dt === '') {
        return '';
    }
    try {
        $t = new DateTimeImmutable($dt);
        $diff = (new DateTimeImmutable('now'))->getTimestamp() - $t->getTimestamp();
        if ($diff < 60) {
            return 'Hace un momento';
        }
        if ($diff < 3600) {
            $m = (int) floor($diff / 60);
            return 'Hace ' . $m . ' min';
        }
        if ($diff < 86400) {
            $h = (int) floor($diff / 3600);
            return 'Hace ' . $h . ' h';
        }
        return $t->format('d/m/Y H:i');
    } catch (Throwable $e) {
        return (string) $dt;
    }
};

$toneWrap = [
    'cyan' => 'bg-light-cyan/15 text-cyan-deep',
    'teal' => 'bg-mint/35 text-teal',
    'orange' => 'bg-brand-yellow/40 text-brand-orange',
    'mint' => 'bg-mint/40 text-teal',
    'error' => 'bg-error-container text-on-error-container',
];

$quickLinks = [];
if ($canAjustes) {
    $quickLinks[] = [
        'href' => 'ajustes.php',
        'label' => 'Ajustes del sistema',
        'desc' => 'Textos, colores, videos, servicios y ubicación',
        'icon' => 'settings',
    ];
}
if ($canAjustes && $videosActivos >= 0) {
    $quickLinks[] = [
        'href' => 'ajustes.php?tab=videos',
        'label' => 'Videos de la landing',
        'desc' => $videosActivos . ' video(s) activo(s)',
        'icon' => 'smart_display',
    ];
}
if ($canAjustes) {
    $quickLinks[] = [
        'href' => 'ajustes.php?tab=servicios',
        'label' => 'Servicios publicados',
        'desc' => $serviciosActivos . ' especialidad(es) activas',
        'icon' => 'medical_services',
    ];
}
if (auth_can('usuarios')) {
    $quickLinks[] = [
        'href' => 'usuarios.php',
        'label' => 'Usuarios y contraseñas',
        'desc' => $usuariosActivos . ' cuenta(s) activas',
        'icon' => 'manage_accounts',
    ];
}
$quickLinks[] = [
    'href' => 'index.php',
    'label' => 'Ver sitio público',
    'desc' => 'Abrir la landing',
    'icon' => 'public',
    'blank' => true,
];

$headerTitle = $copy['welcome'];
$pageTitleAdmin = 'MiDoc - ' . $copy['welcome'];
$roleSubtitle = $copy['subtitle'];
require __DIR__ . '/includes/admin_chrome_start.php';

$firstName = trim(explode(' ', $nombre)[0] ?? $nombre);
$hoy = (new DateTimeImmutable('now'))->format('d/m/Y');
?>

<!-- Bienvenida -->
<section class="rounded-2xl border border-sage/50 bg-mist overflow-hidden">
  <div class="grid lg:grid-cols-12 gap-0">
    <div class="lg:col-span-8 p-5 sm:p-6 lg:p-8">
      <p class="font-label-caps text-label-caps text-on-surface-variant mb-2 tracking-wider">SESIÓN · <?= htmlspecialchars($hoy, ENT_QUOTES, 'UTF-8') ?></p>
      <h3 class="font-headline-md text-2xl lg:text-3xl font-bold text-on-background mb-2">
        Hola, <?= htmlspecialchars($firstName, ENT_QUOTES, 'UTF-8') ?>
      </h3>
      <p class="font-body-md text-on-surface-variant max-w-2xl leading-relaxed"><?= htmlspecialchars($copy['focus'], ENT_QUOTES, 'UTF-8') ?></p>
      <div class="flex flex-wrap gap-2 mt-5">
        <?php if ($canAjustes): ?>
          <a href="ajustes.php" class="inline-flex items-center gap-2 px-4 py-2.5 rounded-xl bg-light-cyan text-white font-bold text-sm hover:bg-cyan-deep transition-colors">
            <span class="material-symbols-outlined text-xl">settings</span>
            Ajustes del sistema
          </a>
        <?php endif; ?>
        <a href="index.php" target="_blank" rel="noopener noreferrer" class="inline-flex items-center gap-2 px-4 py-2.5 rounded-xl border border-outline-variant bg-surface-container-lowest font-bold text-sm text-on-surface hover:border-light-cyan transition-colors">
          <span class="material-symbols-outlined text-xl">open_in_new</span>
          Ver landing
        </a>
      </div>
    </div>
    <div class="lg:col-span-4 border-t lg:border-t-0 lg:border-l border-sage/50 bg-surface-container-low/60 p-5 sm:p-6 lg:p-8 flex flex-col justify-center gap-3">
      <div class="flex items-center gap-3 min-w-0">
        <div class="w-12 h-12 rounded-full bg-light-cyan/20 text-cyan-deep flex items-center justify-center shrink-0">
          <span class="material-symbols-outlined text-2xl">account_circle</span>
        </div>
        <div class="min-w-0">
          <p class="font-bold text-on-background truncate"><?= htmlspecialchars($nombre, ENT_QUOTES, 'UTF-8') ?></p>
          <p class="text-sm text-on-surface-variant truncate"><?= htmlspecialchars($email, ENT_QUOTES, 'UTF-8') ?></p>
        </div>
      </div>
      <div class="flex flex-wrap gap-2">
        <span class="inline-flex items-center rounded-full bg-primary-container/30 text-primary px-3 py-1 text-xs font-bold uppercase tracking-wider"><?= htmlspecialchars($rolNombre, ENT_QUOTES, 'UTF-8') ?></span>
        <?php if (auth_can('usuarios')): ?>
          <span class="inline-flex items-center rounded-full bg-mist border border-sage/60 text-on-surface-variant px-3 py-1 text-xs font-bold"><?= (int) $usuariosActivos ?> usuario(s)</span>
        <?php endif; ?>
      </div>
    </div>
  </div>
</section>

<!-- KPIs reales -->
<section class="grid grid-cols-1 sm:grid-cols-2 xl:grid-cols-4 gap-4">
<?php foreach ($stats as $stat): ?>
  <?php $wrap = $toneWrap[$stat['tone']] ?? $toneWrap['cyan']; ?>
  <article class="bg-mist rounded-2xl border border-sage/50 p-5 hover:border-light-cyan/60 transition-colors min-h-[8.5rem] flex flex-col">
    <div class="flex items-start justify-between gap-3 mb-3">
      <p class="font-label-caps text-label-caps text-on-surface-variant tracking-wider leading-snug"><?= htmlspecialchars($stat['label'], ENT_QUOTES, 'UTF-8') ?></p>
      <div class="w-10 h-10 rounded-xl flex items-center justify-center shrink-0 <?= $wrap ?>">
        <span class="material-symbols-outlined"><?= htmlspecialchars($stat['icon'], ENT_QUOTES, 'UTF-8') ?></span>
      </div>
    </div>
    <p class="text-3xl lg:text-4xl font-bold text-on-background tracking-tight leading-none mb-auto"><?= htmlspecialchars($stat['value'], ENT_QUOTES, 'UTF-8') ?></p>
    <p class="text-sm text-on-surface-variant mt-3 pt-3 border-t border-sage/40"><?= htmlspecialchars($stat['hint'], ENT_QUOTES, 'UTF-8') ?></p>
  </article>
<?php endforeach; ?>
</section>

<!-- Solicitudes reales -->
<section class="bg-mist border border-sage/50 rounded-2xl p-5 sm:p-6 flex flex-col min-h-[22rem]">
  <div class="flex flex-wrap items-center justify-between gap-3 mb-5 pb-4 border-b border-sage/40">
    <div>
      <h2 class="font-headline-md text-lg lg:text-xl font-bold text-on-background">Solicitudes de cita (web)</h2>
      <p class="text-sm text-on-surface-variant mt-0.5">Datos reales del formulario de la landing</p>
    </div>
    <?php if ($citasPendientes > 0): ?>
      <span class="inline-flex items-center gap-1.5 text-xs font-bold uppercase tracking-wider text-on-secondary-fixed bg-brand-yellow/50 px-2.5 py-1 rounded-full">
        <?= (int) $citasPendientes ?> pendiente(s)
      </span>
    <?php endif; ?>
  </div>

  <?php if ($recentCitas === []): ?>
    <div class="flex-1 flex flex-col items-center justify-center text-center py-10 text-on-surface-variant">
      <span class="material-symbols-outlined text-4xl mb-2 text-cyan-deep/50">inbox</span>
      <p class="font-bold text-on-background">Aún no hay solicitudes</p>
      <p class="text-sm mt-1 max-w-sm">Cuando alguien complete el formulario de cita en la web, aparecerán aquí.</p>
    </div>
  <?php else: ?>
    <div class="flex-1 space-y-2">
      <?php foreach ($recentCitas as $cita): ?>
        <?php
          $nombreC = (string) ($cita['nombre_contacto'] ?? '');
          $telC = (string) ($cita['telefono_contacto'] ?? '');
          $msgC = trim((string) ($cita['mensaje'] ?? ''));
          $est = (string) ($cita['estado'] ?? 'pendiente');
          $when = $relativeWhen(isset($cita['created_at']) ? (string) $cita['created_at'] : null);
          $msgShort = $msgC !== '' ? (mb_strlen($msgC) > 110 ? mb_substr($msgC, 0, 110) . '…' : $msgC) : 'Sin mensaje adicional';
        ?>
        <article class="flex items-start gap-3 p-3 rounded-xl border border-sage/40 bg-surface-container-lowest/60 hover:border-light-cyan/50 transition-colors">
          <div class="w-10 h-10 rounded-xl bg-light-cyan/15 text-cyan-deep flex items-center justify-center shrink-0">
            <span class="material-symbols-outlined">event</span>
          </div>
          <div class="flex-1 min-w-0">
            <div class="flex flex-wrap items-center gap-2 mb-0.5">
              <p class="font-bold text-on-background truncate"><?= htmlspecialchars($nombreC, ENT_QUOTES, 'UTF-8') ?></p>
              <span class="text-[10px] font-bold uppercase tracking-wider px-2 py-0.5 rounded-full <?= $estadoTone($est) ?>">
                <?= htmlspecialchars($estadoLabel($est), ENT_QUOTES, 'UTF-8') ?>
              </span>
            </div>
            <p class="text-sm text-on-surface-variant">
              <?= htmlspecialchars($telC, ENT_QUOTES, 'UTF-8') ?>
              <?php if ($telC !== ''): ?>
                · <a class="text-cyan-deep font-bold hover:underline" href="<?= htmlspecialchars(whatsapp_chat_url($telC, 'Hola ' . $nombreC . ', te contactamos de MiDoc por tu solicitud de cita.'), ENT_QUOTES, 'UTF-8') ?>" target="_blank" rel="noopener noreferrer">WhatsApp</a>
              <?php endif; ?>
            </p>
            <p class="text-sm text-on-surface mt-1 landing-break"><?= htmlspecialchars($msgShort, ENT_QUOTES, 'UTF-8') ?></p>
            <p class="text-xs font-bold uppercase tracking-wider text-on-surface-variant mt-1.5"><?= htmlspecialchars($when, ENT_QUOTES, 'UTF-8') ?></p>
          </div>
        </article>
      <?php endforeach; ?>
    </div>
  <?php endif; ?>
</section>

<!-- Accesos reales -->
<section class="bg-mist border border-sage/50 rounded-2xl p-5 sm:p-6">
  <div class="mb-5 pb-4 border-b border-sage/40">
    <h2 class="font-headline-md text-lg lg:text-xl font-bold text-on-background">Accesos rápidos</h2>
    <p class="text-sm text-on-surface-variant mt-0.5">Solo módulos disponibles ahora</p>
  </div>
  <div class="grid grid-cols-1 sm:grid-cols-2 xl:grid-cols-3 gap-3">
    <?php foreach ($quickLinks as $link): ?>
      <?php $blank = !empty($link['blank']); ?>
      <a href="<?= htmlspecialchars($link['href'], ENT_QUOTES, 'UTF-8') ?>"
         <?= $blank ? 'target="_blank" rel="noopener noreferrer"' : '' ?>
         class="flex items-start gap-3 p-4 rounded-xl border border-sage/50 hover:border-light-cyan hover:bg-surface-container-low transition-colors min-h-[5.5rem]">
        <div class="w-11 h-11 rounded-xl bg-light-cyan/15 text-cyan-deep flex items-center justify-center shrink-0">
          <span class="material-symbols-outlined"><?= htmlspecialchars($link['icon'], ENT_QUOTES, 'UTF-8') ?></span>
        </div>
        <div class="min-w-0 flex-1">
          <p class="font-bold text-on-background"><?= htmlspecialchars($link['label'], ENT_QUOTES, 'UTF-8') ?></p>
          <p class="text-sm text-on-surface-variant mt-0.5"><?= htmlspecialchars($link['desc'], ENT_QUOTES, 'UTF-8') ?></p>
        </div>
        <span class="material-symbols-outlined text-on-surface-variant shrink-0">chevron_right</span>
      </a>
    <?php endforeach; ?>
  </div>
</section>

<?php require __DIR__ . '/includes/admin_chrome_end.php'; ?>
