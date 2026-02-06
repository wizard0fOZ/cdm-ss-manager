<?php
  $pageTitle = 'Edit Academic Year';
  $pageSubtitle = 'Update academic year dates or active status.';
  $action = '/academic-years/' . (int)($year['id'] ?? 0);
  $submitLabel = 'Save Changes';

  ob_start();
  require __DIR__ . '/_form.php';
  $content = ob_get_clean();

  require __DIR__ . '/../layout.php';
?>
