<?php
  $pageTitle = 'Edit Session';
  $pageSubtitle = 'Update session timing or order.';
  $action = '/sessions/' . (int)($session['id'] ?? 0);
  $submitLabel = 'Save Changes';

  ob_start();
  require __DIR__ . '/_form.php';
  $content = ob_get_clean();

  require __DIR__ . '/../layout.php';
?>
