<?php
  $pageTitle = $pageTitle ?? 'CDM SS Manager';
  $pageSubtitle = $pageSubtitle ?? 'Manage Sunday School operations with clarity.';
  $userId = (int)($_SESSION['user_id'] ?? 0);
  $userLabel = $userId > 0 ? 'User #' . $userId : 'Staff';
  $csrf = $_SESSION['_csrf'] ?? '';
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

  <script src="/assets/app.js"></script>
</body>
</html>
