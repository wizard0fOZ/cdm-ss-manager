<?php
  $pageTitle = 'Edit Training Record';
  $pageSubtitle = 'Update PSO or formation information.';
  $action = '/training/' . (int)($record['id'] ?? 0);
  $submitLabel = 'Save Changes';

  ob_start();
  require __DIR__ . '/_form.php';
  $content = ob_get_clean();

  require __DIR__ . '/../layout.php';
?>
