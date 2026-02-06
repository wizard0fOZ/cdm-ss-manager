<?php
  $pageTitle = 'Add User';
  $pageSubtitle = 'Create a new system account.';
  $action = '/admin/users';
  $submitLabel = 'Create User';

  ob_start();
?>
  <?php require __DIR__ . '/../_nav.php'; ?>
  <?php $showReset = false; ?>
  <?php require __DIR__ . '/_form.php'; ?>
  <div class="mt-4 text-xs text-slate-500">Temp password will be set to default and must be changed on first login.</div>
<?php
  $content = ob_get_clean();
  require __DIR__ . '/../../layout.php';
?>
