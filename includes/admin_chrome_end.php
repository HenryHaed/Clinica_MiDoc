<?php
/**
 * Cierre del chrome admin + barra inferior tipo app (móvil).
 */
$adminBottomItems = $adminBottomItems ?? [];
$adminMoreItems = $adminMoreItems ?? [];
$navActive = $navActive ?? 'dashboard';
$bottomCols = count($adminBottomItems) + ($adminMoreItems !== [] ? 1 : 0);
if ($bottomCols < 1) {
    $bottomCols = 1;
}
$colClass = match ($bottomCols) {
    2 => 'grid-cols-2',
    3 => 'grid-cols-3',
    4 => 'grid-cols-4',
    default => 'grid-cols-5',
};
?>
</div>
</main>
</div>

<nav class="admin-bottom-nav lg:hidden fixed bottom-0 left-0 right-0 z-50 bg-mist/95 backdrop-blur-xl border-t border-sage/50 shadow-[0_-8px_24px_rgba(0,62,103,0.08)]" aria-label="Navegación del panel">
  <div class="grid <?= $colClass ?> items-stretch px-1 pt-1">
    <?php foreach ($adminBottomItems as $item): ?>
      <?php $isActive = function_exists('admin_item_active') ? admin_item_active($navActive, $item) : false; ?>
      <a href="<?= htmlspecialchars($item['href'], ENT_QUOTES, 'UTF-8') ?>"
         class="flex flex-col items-center justify-center gap-0.5 min-h-12 py-1 text-on-surface-variant <?= $isActive ? 'is-active' : '' ?>">
        <span class="material-symbols-outlined text-[24px]"><?= htmlspecialchars($item['icon'], ENT_QUOTES, 'UTF-8') ?></span>
        <span class="text-[10px] font-bold tracking-wide"><?= htmlspecialchars($item['short'] ?? $item['label'], ENT_QUOTES, 'UTF-8') ?></span>
      </a>
    <?php endforeach; ?>
    <?php if ($adminMoreItems !== []): ?>
      <button type="button" id="admin-more-btn" class="flex flex-col items-center justify-center gap-0.5 min-h-12 py-1 text-on-surface-variant" aria-expanded="false" aria-controls="admin-more-sheet">
        <span class="material-symbols-outlined text-[24px]">menu</span>
        <span class="text-[10px] font-bold tracking-wide">Más</span>
      </button>
    <?php endif; ?>
  </div>
</nav>

<?php if ($adminMoreItems !== []): ?>
<div id="admin-more-sheet" class="hidden lg:hidden fixed inset-0 z-[60]" role="dialog" aria-modal="true" aria-labelledby="admin-more-title">
  <button type="button" id="admin-more-backdrop" class="absolute inset-0 bg-on-background/40" aria-label="Cerrar menú"></button>
  <div class="absolute bottom-0 left-0 right-0 bg-mist rounded-t-2xl border-t border-sage/50 p-4 pb-[max(1rem,env(safe-area-inset-bottom))] shadow-[0_-12px_40px_rgba(0,62,103,0.18)]">
    <div class="w-10 h-1 rounded-full bg-outline-variant mx-auto mb-4"></div>
    <h3 id="admin-more-title" class="font-headline-md text-lg font-bold text-on-background mb-1 px-1">Más opciones</h3>
    <p class="text-xs text-on-surface-variant px-1 mb-3">Módulos aún no disponibles en el sistema</p>
    <div class="grid grid-cols-1 gap-1">
      <?php foreach ($adminMoreItems as $item): ?>
        <div class="flex items-center gap-3 px-3 py-3 rounded-xl text-on-surface-variant/80 bg-surface-container-lowest/60">
          <span class="material-symbols-outlined"><?= htmlspecialchars($item['icon'], ENT_QUOTES, 'UTF-8') ?></span>
          <span class="font-bold flex-1"><?= htmlspecialchars($item['label'], ENT_QUOTES, 'UTF-8') ?></span>
          <span class="text-[10px] font-bold uppercase tracking-wider px-2 py-0.5 rounded-full bg-brand-yellow/40 text-on-secondary-fixed">Pronto</span>
        </div>
      <?php endforeach; ?>
      <a href="index.php" target="_blank" rel="noopener noreferrer" class="flex items-center gap-3 px-3 py-3 rounded-xl text-on-surface hover:bg-surface-container-high">
        <span class="material-symbols-outlined text-cyan-deep">public</span>
        <span class="font-bold">Ver sitio</span>
      </a>
      <a href="dashboard.php?logout=1" class="flex items-center gap-3 px-3 py-3 rounded-xl text-error hover:bg-error-container">
        <span class="material-symbols-outlined">logout</span>
        <span class="font-bold">Cerrar sesión</span>
      </a>
    </div>
  </div>
</div>
<script>
(function () {
  var btn = document.getElementById('admin-more-btn');
  var sheet = document.getElementById('admin-more-sheet');
  var backdrop = document.getElementById('admin-more-backdrop');
  if (!btn || !sheet) return;
  function openSheet(open) {
    sheet.classList.toggle('hidden', !open);
    btn.setAttribute('aria-expanded', open ? 'true' : 'false');
  }
  btn.addEventListener('click', function () { openSheet(sheet.classList.contains('hidden')); });
  if (backdrop) backdrop.addEventListener('click', function () { openSheet(false); });
})();
</script>
<?php endif; ?>
</body>
</html>
