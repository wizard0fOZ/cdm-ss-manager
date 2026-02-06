<?php
  $pageTitle = 'New Announcement';
  $pageSubtitle = 'Share a bulletin with staff or a specific class.';
  $action = '/announcements';
  $submitLabel = 'Create Announcement';
  $pageScripts = '<script>window.CDM_ANNOUNCEMENT_PREVIEW = true;</script>';

  ob_start();
?>
  <?php require __DIR__ . '/_form.php'; ?>
<?php
  $content = ob_get_clean();
  require __DIR__ . '/../layout.php';
?>
