<?php
/**
 * Dashboard — UI según rol autenticado (sin BD aún para métricas reales).
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

// Stats mock según rol (luego se reemplazan por queries reales)
$stats = match ($rolSlug) {
    'doctor' => [
        ['label' => 'Mis citas hoy', 'value' => '7', 'icon' => 'calendar_today', 'tone' => 'primary'],
        ['label' => 'Pacientes en seguimiento', 'value' => '28', 'icon' => 'group', 'tone' => 'primary'],
        ['label' => 'Cirugías programadas', 'value' => '3', 'icon' => 'healing', 'tone' => 'error'],
        ['label' => 'Consultas pendientes', 'value' => '5', 'icon' => 'clinical_notes', 'tone' => 'primary'],
    ],
    'enfermero' => [
        ['label' => 'Pacientes asignados', 'value' => '16', 'icon' => 'group', 'tone' => 'primary'],
        ['label' => 'Signos por registrar', 'value' => '4', 'icon' => 'monitor_heart', 'tone' => 'primary'],
        ['label' => 'Citas de apoyo hoy', 'value' => '9', 'icon' => 'calendar_today', 'tone' => 'primary'],
        ['label' => 'Alertas clínicas', 'value' => '2', 'icon' => 'warning', 'tone' => 'error'],
    ],
    'recepcion' => [
        ['label' => 'Citas de hoy', 'value' => '42', 'icon' => 'calendar_today', 'tone' => 'primary'],
        ['label' => 'Pendientes de confirmar', 'value' => '11', 'icon' => 'pending_actions', 'tone' => 'primary'],
        ['label' => 'Llamadas por devolver', 'value' => '6', 'icon' => 'call', 'tone' => 'primary'],
        ['label' => 'Nuevos contactos web', 'value' => '8', 'icon' => 'mail', 'tone' => 'primary'],
    ],
    default => [ // admin / super_admin
        ['label' => 'Total pacientes', 'value' => '1,248', 'icon' => 'group', 'tone' => 'primary'],
        ['label' => 'Citas hoy', 'value' => '42', 'icon' => 'calendar_today', 'tone' => 'primary'],
        ['label' => 'Cirugías programadas', 'value' => '8', 'icon' => 'healing', 'tone' => 'error'],
        ['label' => 'Ingresos mes', 'value' => '$124k', 'icon' => 'payments', 'tone' => 'primary'],
    ],
};

$activities = match ($rolSlug) {
    'doctor' => [
        ['icon' => 'person', 'text' => '<b>Consulta</b> pendiente con paciente Juan Pérez.', 'when' => 'Hace 15 min'],
        ['icon' => 'healing', 'text' => '<b>Cirugía</b> de hernia confirmada para mañana 09:00.', 'when' => 'Hace 1 h'],
        ['icon' => 'clinical_notes', 'text' => 'Debes completar notas de la consulta #1042.', 'when' => 'Hace 2 h'],
    ],
    'enfermero' => [
        ['icon' => 'monitor_heart', 'text' => 'Registrar signos vitales — habitación / consulta A.', 'when' => 'Hace 10 min'],
        ['icon' => 'group', 'text' => 'Paciente nuevo asignado a tu turno.', 'when' => 'Hace 40 min'],
        ['icon' => 'vaccines', 'text' => 'Preparación preoperatoria pendiente.', 'when' => 'Hace 2 h'],
    ],
    'recepcion' => [
        ['icon' => 'event', 'text' => 'Nueva solicitud web de cita (hernia).', 'when' => 'Hace 5 min'],
        ['icon' => 'call', 'text' => 'Confirmar cita telefónica con Laura G.', 'when' => 'Hace 25 min'],
        ['icon' => 'cancel', 'text' => 'Paciente canceló cita de las 16:00.', 'when' => 'Hace 1 h'],
    ],
    default => [
        ['icon' => 'person_add', 'text' => '<b>Dr. Hernán</b> tiene 3 citas nuevas hoy.', 'when' => 'Hace 10 min'],
        ['icon' => 'description', 'text' => 'Solicitud de cita web #8923 recibida.', 'when' => 'Hace 1 h'],
        ['icon' => 'warning', 'text' => '<b>Sistema:</b> revisar BD / backups.', 'when' => 'Hace 2 h'],
    ],
};
$headerTitle = $copy['welcome'];
$pageTitleAdmin = 'MiDoc - ' . $copy['welcome'];
$roleSubtitle = $copy['subtitle'];
require __DIR__ . '/includes/admin_chrome_start.php';
?>

<section class="bg-mist border border-sage/50 rounded-xl p-5 sm:p-6">
  <p class="font-label-caps text-label-caps text-on-surface-variant mb-2">SESIÓN ACTIVA</p>
  <h3 class="font-headline-md text-xl sm:text-headline-md font-bold text-on-background mb-2">Hola, <?= htmlspecialchars($nombre, ENT_QUOTES, 'UTF-8') ?></h3>
  <p class="font-body-md text-body-md text-on-surface-variant"><?= htmlspecialchars($copy['focus'], ENT_QUOTES, 'UTF-8') ?></p>
</section>

<section class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4 lg:gap-gutter">
<?php foreach ($stats as $stat): ?>
  <?php
    $iconWrap = $stat['tone'] === 'error'
      ? 'bg-error-container text-on-error-container'
      : 'bg-mint/40 text-teal';
  ?>
  <div class="bg-mist p-5 sm:p-6 rounded-xl border border-sage/50 hover:border-brand-orange transition-colors">
    <div class="flex justify-between items-start mb-2">
      <div>
        <p class="font-label-caps text-label-caps text-on-surface-variant mb-1"><?= htmlspecialchars($stat['label'], ENT_QUOTES, 'UTF-8') ?></p>
        <h3 class="text-2xl sm:text-display-lg-mobile font-bold text-on-background"><?= htmlspecialchars($stat['value'], ENT_QUOTES, 'UTF-8') ?></h3>
      </div>
      <div class="w-10 h-10 rounded-full flex items-center justify-center <?= $iconWrap ?>">
        <span class="material-symbols-outlined"><?= htmlspecialchars($stat['icon'], ENT_QUOTES, 'UTF-8') ?></span>
      </div>
    </div>
  </div>
<?php endforeach; ?>
</section>

<div class="grid grid-cols-1 lg:grid-cols-3 gap-4 lg:gap-gutter">
<section class="lg:col-span-2 bg-mist border border-sage/50 rounded-xl p-5 sm:p-6">
  <div class="flex justify-between items-center mb-6 border-b border-outline-variant pb-4">
    <h2 class="font-headline-md text-lg sm:text-headline-md text-on-background font-bold">Actividad reciente</h2>
  </div>
  <div class="space-y-4">
  <?php foreach ($activities as $act): ?>
    <div class="flex items-start gap-4 p-3 hover:bg-surface-container-low rounded-lg transition-colors">
      <div class="w-10 h-10 rounded-full bg-surface-variant flex items-center justify-center text-on-surface flex-shrink-0">
        <span class="material-symbols-outlined"><?= htmlspecialchars($act['icon'], ENT_QUOTES, 'UTF-8') ?></span>
      </div>
      <div class="flex-1 min-w-0">
        <p class="font-body-md text-body-md text-on-background"><?= $act['text'] ?></p>
        <p class="font-label-caps text-label-caps text-on-surface-variant mt-1"><?= htmlspecialchars($act['when'], ENT_QUOTES, 'UTF-8') ?></p>
      </div>
    </div>
  <?php endforeach; ?>
  </div>
</section>

<section class="bg-mist border border-sage/50 rounded-xl p-5 sm:p-6 flex flex-col">
  <div class="mb-6 border-b border-outline-variant pb-4">
    <h2 class="font-headline-md text-lg sm:text-headline-md text-on-background font-bold">Accesos rápidos</h2>
  </div>
  <div class="flex-1 space-y-3">
    <?php if (auth_can('citas')): ?>
    <a href="#" class="w-full flex items-center justify-between p-3 rounded-lg border border-outline-variant hover:border-primary text-on-background transition-colors min-h-12">
      <span class="font-body-md text-body-md font-bold">Gestionar citas</span>
      <span class="material-symbols-outlined text-primary">calendar_today</span>
    </a>
    <?php endif; ?>
    <?php if (auth_can('pacientes')): ?>
    <a href="#" class="w-full flex items-center justify-between p-3 rounded-lg border border-outline-variant hover:border-primary text-on-background transition-colors min-h-12">
      <span class="font-body-md text-body-md font-bold">Ver pacientes</span>
      <span class="material-symbols-outlined text-primary">group</span>
    </a>
    <?php endif; ?>
    <?php if (auth_can('cms')): ?>
    <a href="ajustes.php?tab=contenido" class="w-full flex items-center justify-between p-3 rounded-lg border border-outline-variant hover:border-primary text-on-background transition-colors min-h-12">
      <span class="font-body-md text-body-md font-bold">Editar landing</span>
      <span class="material-symbols-outlined text-primary">web</span>
    </a>
    <?php endif; ?>
    <?php if (auth_can('usuarios')): ?>
    <a href="#" class="w-full flex items-center justify-between p-3 rounded-lg border border-outline-variant hover:border-primary text-on-background transition-colors min-h-12">
      <span class="font-body-md text-body-md font-bold">Administrar usuarios</span>
      <span class="material-symbols-outlined text-primary">manage_accounts</span>
    </a>
    <?php endif; ?>
    <?php if (auth_can('config')): ?>
    <a href="ajustes.php" class="w-full flex items-center justify-between p-3 rounded-xl border border-light-cyan/30 bg-mint/10 hover:border-light-cyan text-on-background transition-colors min-h-12">
      <span class="font-body-md text-body-md font-bold">Ajustes del sistema</span>
      <span class="material-symbols-outlined text-cyan-deep">settings</span>
    </a>
    <?php endif; ?>
  </div>
</section>
</div>

<?php require __DIR__ . '/includes/admin_chrome_end.php'; ?>
