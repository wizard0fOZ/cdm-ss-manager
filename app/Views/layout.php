<?php
  $pageTitle = $pageTitle ?? 'CDM SS Manager';
  $pageSubtitle = $pageSubtitle ?? 'Manage Sunday School operations with clarity.';
  $userId = (int)($_SESSION['user_id'] ?? 0);
  $userLabel = $userId > 0 ? 'User #' . $userId : 'Staff';
  $csrf = $_SESSION['_csrf'] ?? '';
  $maintenanceBanner = null;
  try {
    if ($userId > 0) {
      $pdo = \App\Core\Db\Db::pdo();
      $stmt = $pdo->prepare('SELECT setting_key, setting_value FROM system_settings WHERE setting_key IN (?, ?)');
      $stmt->execute(['maintenance_mode', 'maintenance_message']);
      $mode = null;
      $message = null;
      foreach ($stmt->fetchAll() as $row) {
        if ($row['setting_key'] === 'maintenance_mode') $mode = strtoupper(trim((string)$row['setting_value']));
        if ($row['setting_key'] === 'maintenance_message') $message = (string)$row['setting_value'];
      }
      if ($mode && $mode !== 'OFF') {
        $stmt = $pdo->prepare('SELECT 1 FROM user_roles ur JOIN roles r ON r.id = ur.role_id WHERE ur.user_id = ? AND r.code = ? LIMIT 1');
        $stmt->execute([$userId, 'SYSADMIN']);
        if ($stmt->fetchColumn()) {
          $maintenanceBanner = [
            'mode' => $mode,
            'message' => $message,
          ];
        }
      }
    }
  } catch (\Throwable $e) {
    $maintenanceBanner = null;
  }
?>
<!doctype html>
<html lang="en">
<?php require __DIR__ . '/partials/head.php'; ?>
<body class="bg-slate-50 text-slate-900">
  <div class="min-h-screen bg-soft-grid">
    <div class="relative flex min-h-screen">
      <div class="fixed inset-0 z-30 hidden bg-slate-900/30 lg:hidden" data-sidebar-overlay></div>

      <?php require __DIR__ . '/partials/sidebar.php'; ?>

      <div class="flex min-h-screen flex-1 flex-col">
        <?php require __DIR__ . '/partials/nav.php'; ?>

        <?php if ($maintenanceBanner): ?>
          <div class="mx-6 mt-4 rounded-xl border border-amber-200 bg-amber-50 px-4 py-3 text-sm text-amber-900">
            <span class="font-semibold">Maintenance mode is ON (<?= htmlspecialchars($maintenanceBanner['mode']) ?>).</span>
            <?php if (!empty($maintenanceBanner['message'])): ?>
              <span class="text-amber-700"> <?= htmlspecialchars($maintenanceBanner['message']) ?></span>
            <?php endif; ?>
          </div>
        <?php endif; ?>

        <?php require __DIR__ . '/partials/toast.php'; ?>

        <main class="flex-1 px-6 py-6">
          <div class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm">
            <?= $content ?? '' ?>
          </div>
        </main>

        <?php require __DIR__ . '/partials/footer.php'; ?>
      </div>
    </div>
  </div>

  <form id="logout-form" method="post" action="/logout" class="hidden">
    <input type="hidden" name="_csrf" value="<?= htmlspecialchars($csrf) ?>">
  </form>

  <?php require __DIR__ . '/partials/modal.php'; ?>

  <script src="/assets/choices.min.js"></script>
  <script defer src="https://unpkg.com/alpinejs@3.13.8/dist/cdn.min.js"></script>
  <script src="https://unpkg.com/htmx.org@1.9.12"></script>
  <?= $pageScripts ?? '' ?>
  <script src="/assets/app.js"></script>
</body>
</html>
