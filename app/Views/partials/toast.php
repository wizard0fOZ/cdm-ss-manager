<?php
  use App\Core\Support\Flash;
  $flash = $flash ?? Flash::get();
  if (!empty($flash) && is_array($flash)):
    $type = $flash['type'] ?? 'info';
    $message = $flash['message'] ?? '';
    $colors = [
      'success' => 'bg-emerald-50 text-emerald-800 border-emerald-200',
      'error' => 'bg-red-50 text-red-700 border-red-200',
      'warning' => 'bg-amber-50 text-amber-800 border-amber-200',
      'info' => 'bg-sky-50 text-sky-800 border-sky-200',
    ];
    $icons = [
      'success' => '<svg class="h-4 w-4 flex-shrink-0" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"/><path d="M22 4 12 14.01l-3-3"/></svg>',
      'error' => '<svg class="h-4 w-4 flex-shrink-0" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"/><path d="m15 9-6 6"/><path d="m9 9 6 6"/></svg>',
      'warning' => '<svg class="h-4 w-4 flex-shrink-0" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M10.29 3.86 1.82 18a2 2 0 0 0 1.71 3h16.94a2 2 0 0 0 1.71-3L13.71 3.86a2 2 0 0 0-3.42 0z"/><path d="M12 9v4"/><path d="M12 17h.01"/></svg>',
      'info' => '<svg class="h-4 w-4 flex-shrink-0" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"/><path d="M12 16v-4"/><path d="M12 8h.01"/></svg>',
    ];
    $colorClass = $colors[$type] ?? $colors['info'];
    $icon = $icons[$type] ?? $icons['info'];
?>
  <div class="mx-6 mt-4 flex items-center gap-3 rounded-xl border px-4 py-3 text-sm toast-enter <?= $colorClass ?>" data-toast>
    <?= $icon ?>
    <span><?= htmlspecialchars($message) ?></span>
  </div>
  <script>
    (function () {
      const toast = document.querySelector('[data-toast]');
      if (!toast) return;
      setTimeout(() => {
        toast.classList.remove('toast-enter');
        toast.classList.add('toast-exit');
        toast.addEventListener('animationend', () => toast.remove(), { once: true });
      }, 4000);
    })();
  </script>
<?php endif; ?>
