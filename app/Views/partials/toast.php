<?php
  $flash = $flash ?? ($_SESSION['flash'] ?? null);
  if (!empty($flash) && is_array($flash)):
    $type = $flash['type'] ?? 'info';
    $message = $flash['message'] ?? '';
    $colors = [
      'success' => 'bg-emerald-50 text-emerald-800 border-emerald-200',
      'error' => 'bg-red-50 text-red-700 border-red-200',
      'warning' => 'bg-amber-50 text-amber-800 border-amber-200',
      'info' => 'bg-sky-50 text-sky-800 border-sky-200',
    ];
    $colorClass = $colors[$type] ?? $colors['info'];
?>
  <div class="mx-6 mt-6 rounded-xl border px-4 py-3 text-sm <?= $colorClass ?>">
    <?= htmlspecialchars($message) ?>
  </div>
<?php endif; ?>
