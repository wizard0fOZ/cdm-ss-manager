<?php
  $pageTitle = 'Add Session';
  $pageSubtitle = 'Create a Sunday time block.';
  $action = '/sessions';
  $submitLabel = 'Create Session';

  ob_start();
  require __DIR__ . '/_form.php';
  $content = ob_get_clean();

  require __DIR__ . '/../layout.php';
?>
