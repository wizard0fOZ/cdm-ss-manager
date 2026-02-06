<?php
  $pageTitle = 'Add Term';
  $pageSubtitle = 'Define a term within an academic year.';
  $action = '/terms';
  $submitLabel = 'Create Term';

  ob_start();
  require __DIR__ . '/_form.php';
  $content = ob_get_clean();

  require __DIR__ . '/../layout.php';
?>
