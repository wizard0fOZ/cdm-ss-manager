<?php
  $pageTitle = 'Add Lesson Plan';
  $pageSubtitle = !empty($copyMode) ? 'Copied from a previous lesson. Update the date and content.' : 'Draft a new lesson for your class.';
  $action = '/lessons';
  $submitLabel = 'Create Lesson';

  ob_start();
  require __DIR__ . '/_form.php';
  $content = ob_get_clean();

  require __DIR__ . '/../layout.php';
?>
