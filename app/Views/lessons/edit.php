<?php
  $pageTitle = 'Edit Lesson Plan';
  $pageSubtitle = 'Update lesson content or publish it.';
  $action = '/lessons/' . (int)($lesson['id'] ?? 0);
  $submitLabel = 'Save Changes';

  ob_start();
  require __DIR__ . '/_form.php';
  $content = ob_get_clean();

  require __DIR__ . '/../layout.php';
?>
