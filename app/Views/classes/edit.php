<?php
  $pageTitle = 'Edit Class';
  $pageSubtitle = 'Update class schedule and program details.';
  $action = '/classes/' . (int)($class['id'] ?? 0);
  $submitLabel = 'Save Changes';

  ob_start();
  require __DIR__ . '/_form.php';
  $content = ob_get_clean();

  require __DIR__ . '/../layout.php';
?>
