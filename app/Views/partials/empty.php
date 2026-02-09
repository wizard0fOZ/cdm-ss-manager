<div class="empty-state">
  <div class="empty-state-icon">
    <svg class="h-6 w-6" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round">
      <path d="M13 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V9z"/>
      <path d="M13 2v7h7"/>
    </svg>
  </div>
  <div class="text-sm text-slate-500"><?= htmlspecialchars($message ?? 'No data found.') ?></div>
  <?php if (!empty($actionLabel) && !empty($actionHref)): ?>
    <a href="<?= htmlspecialchars($actionHref) ?>" class="btn btn-primary btn-sm"><?= htmlspecialchars($actionLabel) ?></a>
  <?php endif; ?>
</div>
