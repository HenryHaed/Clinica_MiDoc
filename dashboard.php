<?php
/**
 * Dashboard — panel por rol (métricas mock hasta conectar BD).
 */
declare(strict_types=1);

require_once __DIR__ . '/includes/auth.php';
require_once __DIR__ . '/includes/theme.php';

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
        'focus' => 'Gestiona usuarios, configuración crítica, CMS y toda la operación.',
    ],
    'admin' => [
        'subtitle' => 'Gestión operativa',
        'welcome' => 'Panel de Administración',
        'focus' => 'Citas, pacientes, reportes y contenidos de la landing.',
    ],
    'doctor' => [
        'subtitle' => 'Área clínica',
        'welcome' => 'Panel del Doctor',
        'focus' => 'Tus citas, pacientes y registro clínico.',
    ],
    'enfermero' => [
        'subtitle' => 'Apoyo clínico',
        'welcome' => 'Panel de Enfermería',
        'focus' => 'Seguimiento de pacientes, signos y apoyo en consultas.',
    ],
    'recepcion' => [
        'subtitle' => 'Atención al paciente',
        'welcome' => 'Panel de Recepción',
        'focus' => 'Agenda de citas y contacto con pacientes.',
    ],
];

$copy = $roleCopy[$rolSlug] ?? $roleCopy['admin'];

$stats = match ($rolSlug) {
    'doctor' => [
        ['label' => 'Mis citas hoy', 'value' => '7', 'icon' => 'calendar_today', 'tone' => 'cyan', 'hint' => '3 por confirmar'],
        ['label' => 'En seguimiento', 'value' => '28', 'icon' => 'group', 'tone' => 'teal', 'hint' => 'Pacientes activos'],
        ['label' => 'Cirugías', 'value' => '3', 'icon' => 'healing', 'tone' => 'orange', 'hint' => 'Esta semana'],
        ['label' => 'Notas pendientes', 'value' => '5', 'icon' => 'clinical_notes', 'tone' => 'error', 'hint' => 'Por completar'],
    ],
    'enfermero' => [
        ['label' => 'Asignados', 'value' => '16', 'icon' => 'group', 'tone' => 'cyan', 'hint' => 'Tu turno'],
        ['label' => 'Signos vitales', 'value' => '4', 'icon' => 'monitor_heart', 'tone' => 'teal', 'hint' => 'Por registrar'],
        ['label' => 'Apoyo hoy', 'value' => '9', 'icon' => 'calendar_today', 'tone' => 'orange', 'hint' => 'Consultas'],
        ['label' => 'Alertas', 'value' => '2', 'icon' => 'warning', 'tone' => 'error', 'hint' => 'Requieren atención'],
    ],
    'recepcion' => [
        ['label' => 'Citas de hoy', 'value' => '42', 'icon' => 'calendar_today', 'tone' => 'cyan', 'hint' => 'Agenda completa'],
        ['label' => 'Por confirmar', 'value' => '11', 'icon' => 'pending_actions', 'tone' => 'orange', 'hint' => 'Llamadas'],
        ['label' => 'Devolver llamada', 'value' => '6', 'icon' => 'call', 'tone' => 'teal', 'hint' => 'Pendientes'],
        ['label' => 'Contactos web', 'value' => '8', 'icon' => 'mail', 'tone' => 'mint', 'hint' => 'Últimas 24 h'],
    ],
    default => [
        ['label' => 'Pacientes', 'value' => '1,248', 'icon' => 'group', 'tone' => 'cyan', 'hint' => 'Registro total'],
        ['label' => 'Citas hoy', 'value' => '42', 'icon' => 'calendar_today', 'tone' => 'teal', 'hint' => 'En la clínica'],
        ['label' => 'Cirugías', 'value' => '8', 'icon' => 'healing', 'tone' => 'orange', 'hint' => 'Programadas'],
        ['label' => 'Ingresos mes', 'value' => '$124k', 'icon' => 'payments', 'tone' => 'mint', 'hint' => 'Estimado'],
    ],
};

$activities = match ($rolSlug) {
    'doctor' => [
        ['icon' => 'person', 'text' => '<b>Consulta</b> pendiente con paciente Juan Pérez.', 'when' => 'Hace 15 min'],
        ['icon' => 'healing', 'text' => '<b>Cirugía</b> de hernia confirmada para mañana 09:00.', 'when' => 'Hace 1 h'],
        ['icon' => 'clinical_notes', 'text' => 'Debes completar notas de la consulta #1042.', 'when' => 'Hace 2 h'],
        ['icon' => 'schedule', 'text' => 'Recordatorio: revisión postoperatoria a las 16:30.', 'when' => 'Hace 3 h'],
    ],
    'enfermero' => [
        ['icon' => 'monitor_heart', 'text' => 'Registrar signos vitales — consulta A.', 'when' => 'Hace 10 min'],
        ['icon' => 'group', 'text' => 'Paciente nuevo asignado a tu turno.', 'when' => 'Hace 40 min'],
        ['icon' => 'vaccines', 'text' => 'Preparación preoperatoria pendiente.', 'when' => 'Hace 2 h'],
        ['icon' => 'assignment', 'text' => 'Checklist de insumos del quirófano actualizado.', 'when' => 'Hace 4 h'],
    ],
    'recepcion' => [
        ['icon' => 'event', 'text' => 'Nueva solicitud web de cita (hernia).', 'when' => 'Hace 5 min'],
        ['icon' => 'call', 'text' => 'Confirmar cita telefónica con Laura G.', 'when' => 'Hace 25 min'],
        ['icon' => 'cancel', 'text' => 'Paciente canceló cita de las 16:00.', 'when' => 'Hace 1 h'],
        ['icon' => 'mail', 'text' => '3 mensajes nuevos desde el formulario web.', 'when' => 'Hace 2 h'],
    ],
    default => [
        ['icon' => 'person_add', 'text' => '<b>Dr. Hernán</b> tiene 3 citas nuevas hoy.', 'when' => 'Hace 10 min'],
        ['icon' => 'description', 'text' => 'Solicitud de cita web #8923 recibida.', 'when' => 'Hace 1 h'],
        ['icon' => 'palette', 'text' => 'Colores y textos del sitio editables en Ajustes.', 'when' => 'Hace 2 h'],
        ['icon' => 'cloud_done', 'text' => 'Sistema listo para operación en Hostinger.', 'when' => 'Hoy'],
    ],
};

$toneWrap = [
    'cyan' => 'bg-light-cyan/15 text-cyan-deep',
    'teal' => 'bg-mint/35 text-teal',
    'orange' => 'bg-brand-yellow/40 text-brand-orange',
    'mint' => 'bg-mint/40 text-teal',
    'error' => 'bg-error-container text-on-error-container',
];

$quickLinks = [];
if (auth_can('citas')) {
    $quickLinks[] = ['href' => '#', 'label' => 'Gestionar citas', 'desc' => 'Agenda y confirmaciones', 'icon' => 'calendar_today', 'ready' => false];
}
if (auth_can('pacientes')) {
    $quickLinks[] = ['href' => '#', 'label' => 'Pacientes', 'desc' => 'Historial y fichas', 'icon' => 'group', 'ready' => false];
}
if (auth_can('clinico')) {
    $quickLinks[] = ['href' => '#', 'label' => 'Área clínica', 'desc' => 'Notas y seguimiento', 'icon' => 'medical_services', 'ready' => false];
}
if (auth_can('cms')) {
    $quickLinks[] = ['href' => 'ajustes.php?tab=contenido', 'label' => 'Editar landing', 'desc' => 'Textos e imágenes', 'icon' => 'web', 'ready' => true];
}
if (auth_can('config')) {
    $quickLinks[] = ['href' => 'ajustes.php?tab=colores', 'label' => 'Colores del sitio', 'desc' => 'Paleta visual', 'icon' => 'palette', 'ready' => true];
    $quickLinks[] = ['href' => 'ajustes.php', 'label' => 'Ajustes del sistema', 'desc' => 'CMS y configuración', 'icon' => 'settings', 'ready' => true];
}
if (auth_can('usuarios')) {
    $quickLinks[] = ['href' => '#', 'label' => 'Usuarios', 'desc' => 'Roles y accesos', 'icon' => 'manage_accounts', 'ready' => false];
}
$quickLinks[] = ['href' => 'index.php', 'label' => 'Ver sitio público', 'desc' => 'Abrir landing', 'icon' => 'public', 'ready' => true, 'blank' => true];

$agenda = match ($rolSlug) {
    'doctor' => [
        ['time' => '09:00', 'title' => 'Consulta — Juan Pérez', 'meta' => 'Control postoperatorio'],
        ['time' => '10:30', 'title' => 'Consulta — María López', 'meta' => 'Evaluación hernia'],
        ['time' => '14:00', 'title' => 'Cirugía programada', 'meta' => 'Quirófano 2'],
        ['time' => '16:30', 'title' => 'Revisión pediátrica', 'meta' => 'Paciente menor'],
    ],
    'enfermero' => [
        ['time' => '08:30', 'title' => 'Signos vitales — sala A', 'meta' => '3 pacientes'],
        ['time' => '11:00', 'title' => 'Apoyo consulta', 'meta' => 'Dr. Caihuara'],
        ['time' => '13:00', 'title' => 'Preparación preoperatoria', 'meta' => 'Quirófano'],
        ['time' => '15:30', 'title' => 'Entrega de turno', 'meta' => 'Reporte clínico'],
    ],
    'recepcion' => [
        ['time' => '08:00', 'title' => 'Apertura de agenda', 'meta' => 'Confirmar llegadas'],
        ['time' => '10:00', 'title' => 'Llamadas pendientes', 'meta' => '6 contactos'],
        ['time' => '12:30', 'title' => 'Solicitudes web', 'meta' => 'Revisar formulario'],
        ['time' => '17:00', 'title' => 'Cierre de caja / agenda', 'meta' => 'Resumen del día'],
    ],
    default => [
        ['time' => '08:00', 'title' => 'Revisión operativa', 'meta' => 'Citas y personal'],
        ['time' => '11:00', 'title' => 'Contenido landing', 'meta' => 'Ajustes CMS'],
        ['time' => '14:00', 'title' => 'Seguimiento de leads web', 'meta' => 'Formulario de citas'],
        ['time' => '18:00', 'title' => 'Backup / disponibilidad', 'meta' => 'Hostinger'],
    ],
};

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
        <?php if (auth_can('config') || auth_can('cms')): ?>
          <a href="ajustes.php" class="inline-flex items-center gap-2 px-4 py-2.5 rounded-xl bg-light-cyan text-white font-bold text-sm hover:bg-cyan-deep transition-colors">
            <span class="material-symbols-outlined text-xl">tune</span>
            Ir a Ajustes
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
        <span class="inline-flex items-center rounded-full bg-mist border border-sage/60 text-on-surface-variant px-3 py-1 text-xs font-bold uppercase tracking-wider"><?= htmlspecialchars($rolSlug, ENT_QUOTES, 'UTF-8') ?></span>
      </div>
    </div>
  </div>
</section>

<!-- KPIs -->
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

<!-- Actividad + Agenda -->
<div class="grid grid-cols-1 xl:grid-cols-12 gap-4 lg:gap-5">
  <section class="xl:col-span-7 bg-mist border border-sage/50 rounded-2xl p-5 sm:p-6 flex flex-col min-h-[22rem]">
    <div class="flex items-center justify-between gap-3 mb-5 pb-4 border-b border-sage/40">
      <div>
        <h2 class="font-headline-md text-lg lg:text-xl font-bold text-on-background">Actividad reciente</h2>
        <p class="text-sm text-on-surface-variant mt-0.5">Últimos movimientos del panel</p>
      </div>
      <span class="hidden sm:inline-flex items-center gap-1 text-xs font-bold uppercase tracking-wider text-on-surface-variant bg-surface-container-low px-2.5 py-1 rounded-full">
        <span class="w-1.5 h-1.5 rounded-full bg-teal"></span>
        En vivo
      </span>
    </div>
    <div class="flex-1 space-y-2">
    <?php foreach ($activities as $act): ?>
      <div class="flex items-start gap-3 p-3 rounded-xl hover:bg-surface-container-low transition-colors">
        <div class="w-10 h-10 rounded-xl bg-surface-container text-cyan-deep flex items-center justify-center shrink-0">
          <span class="material-symbols-outlined"><?= htmlspecialchars($act['icon'], ENT_QUOTES, 'UTF-8') ?></span>
        </div>
        <div class="flex-1 min-w-0">
          <p class="font-body-md text-on-background leading-snug"><?= $act['text'] ?></p>
          <p class="text-xs font-bold uppercase tracking-wider text-on-surface-variant mt-1"><?= htmlspecialchars($act['when'], ENT_QUOTES, 'UTF-8') ?></p>
        </div>
      </div>
    <?php endforeach; ?>
    </div>
  </section>

  <section class="xl:col-span-5 bg-mist border border-sage/50 rounded-2xl p-5 sm:p-6 flex flex-col min-h-[22rem]">
    <div class="mb-5 pb-4 border-b border-sage/40">
      <h2 class="font-headline-md text-lg lg:text-xl font-bold text-on-background">Agenda de hoy</h2>
      <p class="text-sm text-on-surface-variant mt-0.5">Resumen orientativo del día</p>
    </div>
    <div class="flex-1 space-y-3">
      <?php foreach ($agenda as $slot): ?>
        <div class="flex gap-3 items-start p-3 rounded-xl border border-sage/40 bg-surface-container-lowest/70">
          <div class="shrink-0 w-14 text-center">
            <p class="text-sm font-bold text-cyan-deep tabular-nums"><?= htmlspecialchars($slot['time'], ENT_QUOTES, 'UTF-8') ?></p>
          </div>
          <div class="min-w-0 flex-1 border-l border-sage/50 pl-3">
            <p class="font-bold text-on-background text-sm sm:text-base leading-snug"><?= htmlspecialchars($slot['title'], ENT_QUOTES, 'UTF-8') ?></p>
            <p class="text-sm text-on-surface-variant mt-0.5"><?= htmlspecialchars($slot['meta'], ENT_QUOTES, 'UTF-8') ?></p>
          </div>
        </div>
      <?php endforeach; ?>
    </div>
  </section>
</div>

<!-- Accesos rápidos -->
<section class="bg-mist border border-sage/50 rounded-2xl p-5 sm:p-6">
  <div class="mb-5 pb-4 border-b border-sage/40">
    <h2 class="font-headline-md text-lg lg:text-xl font-bold text-on-background">Accesos rápidos</h2>
    <p class="text-sm text-on-surface-variant mt-0.5">Módulos listos y próximos a activar</p>
  </div>
  <div class="grid grid-cols-1 sm:grid-cols-2 xl:grid-cols-3 gap-3">
    <?php foreach ($quickLinks as $link): ?>
      <?php
        $ready = !empty($link['ready']);
        $blank = !empty($link['blank']);
        $classes = $ready
          ? 'border-sage/50 hover:border-light-cyan hover:bg-surface-container-low cursor-pointer'
          : 'border-dashed border-outline-variant/80 bg-surface-container-lowest/40 opacity-90 cursor-default';
      ?>
      <?php if ($ready): ?>
        <a href="<?= htmlspecialchars($link['href'], ENT_QUOTES, 'UTF-8') ?>"
           <?= $blank ? 'target="_blank" rel="noopener noreferrer"' : '' ?>
           class="flex items-start gap-3 p-4 rounded-xl border transition-colors min-h-[5.5rem] <?= $classes ?>">
          <div class="w-11 h-11 rounded-xl bg-light-cyan/15 text-cyan-deep flex items-center justify-center shrink-0">
            <span class="material-symbols-outlined"><?= htmlspecialchars($link['icon'], ENT_QUOTES, 'UTF-8') ?></span>
          </div>
          <div class="min-w-0 flex-1">
            <p class="font-bold text-on-background"><?= htmlspecialchars($link['label'], ENT_QUOTES, 'UTF-8') ?></p>
            <p class="text-sm text-on-surface-variant mt-0.5"><?= htmlspecialchars($link['desc'], ENT_QUOTES, 'UTF-8') ?></p>
          </div>
          <span class="material-symbols-outlined text-on-surface-variant shrink-0">chevron_right</span>
        </a>
      <?php else: ?>
        <div class="flex items-start gap-3 p-4 rounded-xl border min-h-[5.5rem] <?= $classes ?>" title="Módulo en preparación">
          <div class="w-11 h-11 rounded-xl bg-surface-container text-on-surface-variant flex items-center justify-center shrink-0">
            <span class="material-symbols-outlined"><?= htmlspecialchars($link['icon'], ENT_QUOTES, 'UTF-8') ?></span>
          </div>
          <div class="min-w-0 flex-1">
            <div class="flex flex-wrap items-center gap-2">
              <p class="font-bold text-on-background"><?= htmlspecialchars($link['label'], ENT_QUOTES, 'UTF-8') ?></p>
              <span class="text-[10px] font-bold uppercase tracking-wider px-2 py-0.5 rounded-full bg-brand-yellow/50 text-on-secondary-fixed">Próximamente</span>
            </div>
            <p class="text-sm text-on-surface-variant mt-0.5"><?= htmlspecialchars($link['desc'], ENT_QUOTES, 'UTF-8') ?></p>
          </div>
        </div>
      <?php endif; ?>
    <?php endforeach; ?>
  </div>
</section>

<?php require __DIR__ . '/includes/admin_chrome_end.php'; ?>
