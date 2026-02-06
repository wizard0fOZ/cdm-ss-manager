<?php
  $pageTitle = 'Edit Calendar Event';
  $pageSubtitle = 'Adjust date or scope for this event.';
  $action = '/calendar/' . (int)($event['id'] ?? 0);
  $submitLabel = 'Update Event';
  $pageScripts = '<script>window.CDM_CALENDAR_FORM = true;</script>';

  ob_start();
?>
  <?php require __DIR__ . '/_form.php'; ?>
<?php
  $content = ob_get_clean();
  require __DIR__ . '/../layout.php';
?>
