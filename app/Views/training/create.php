<?php
  $pageTitle = 'Add Training Record';
  $pageSubtitle = 'Coordinator only. Link evidence from Google Drive or OneDrive.';
  $action = '/training';
  $submitLabel = 'Create Record';

  ob_start();
  require __DIR__ . '/_form.php';
  $content = ob_get_clean();

  require __DIR__ . '/../layout.php';
?>
