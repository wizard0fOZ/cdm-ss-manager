<?php
  $pageTitle = 'New Calendar Event';
  $pageSubtitle = 'Add important dates for staff and classes.';
  $action = '/calendar';
  $submitLabel = 'Create Event';
  $pageScripts = '<script>window.CDM_CALENDAR_FORM = true;</script>';

  ob_start();
?>
  <?php require __DIR__ . '/_form.php'; ?>
<?php
  $content = ob_get_clean();
  require __DIR__ . '/../layout.php';
?>
