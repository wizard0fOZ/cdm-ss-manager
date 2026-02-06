<?php
  $pageTitle = 'Edit User';
  $pageSubtitle = 'Update account details and roles.';
  $action = '/admin/users/' . (int)($user['id'] ?? 0);
  $submitLabel = 'Update User';

  ob_start();
?>
  <?php require __DIR__ . '/../_nav.php'; ?>
  <?php $showReset = true; ?>
  <?php require __DIR__ . '/_form.php'; ?>
  <div class="mt-4 text-xs text-slate-500">Reset password will set default temp password and force change at next login.</div>
<?php
  $content = ob_get_clean();
  require __DIR__ . '/../../layout.php';
?>
