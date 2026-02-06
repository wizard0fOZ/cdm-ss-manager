<?php
  $pageTitle = 'Edit Student';
  $pageSubtitle = 'Update student profile, guardians, and sacrament info.';
  $action = '/students/' . (int)($student['id'] ?? 0);
  $submitLabel = 'Save Changes';

  ob_start();
  require __DIR__ . '/_form.php';
  $content = ob_get_clean();

  require __DIR__ . '/../layout.php';
?>
