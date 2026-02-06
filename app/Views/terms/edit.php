<?php
  $pageTitle = 'Edit Term';
  $pageSubtitle = 'Update term dates and labels.';
  $action = '/terms/' . (int)($term['id'] ?? 0);
  $submitLabel = 'Save Changes';

  ob_start();
  require __DIR__ . '/_form.php';
  $content = ob_get_clean();

  require __DIR__ . '/../layout.php';
?>
