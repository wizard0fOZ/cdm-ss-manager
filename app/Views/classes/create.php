<?php
  $pageTitle = 'Add Class';
  $pageSubtitle = 'Create a class tied to a session and program.';
  $action = '/classes';
  $submitLabel = 'Create Class';

  ob_start();
  require __DIR__ . '/_form.php';
  $content = ob_get_clean();

  require __DIR__ . '/../layout.php';
?>
