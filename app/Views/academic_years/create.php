<?php
  $pageTitle = 'Add Academic Year';
  $pageSubtitle = 'Create a new academic year window.';
  $action = '/academic-years';
  $submitLabel = 'Create Year';

  ob_start();
  require __DIR__ . '/_form.php';
  $content = ob_get_clean();

  require __DIR__ . '/../layout.php';
?>
