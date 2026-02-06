<?php
  $pageTitle = 'Add Student';
  $pageSubtitle = 'Register a new student and capture guardian details.';
  $action = '/students';
  $submitLabel = 'Create Student';

  ob_start();
  require __DIR__ . '/_form.php';
  $content = ob_get_clean();

  require __DIR__ . '/../layout.php';
?>
