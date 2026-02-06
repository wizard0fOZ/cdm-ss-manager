<?php
  $pageTitle = 'Edit Announcement';
  $pageSubtitle = 'Adjust timing or message for staff.';
  $action = '/announcements/' . (int)($announcement['id'] ?? 0);
  $submitLabel = 'Update Announcement';
  $pageScripts = '<script>window.CDM_ANNOUNCEMENT_PREVIEW = true;</script>';

  ob_start();
?>
  <?php require __DIR__ . '/_form.php'; ?>
<?php
  $content = ob_get_clean();
  require __DIR__ . '/../layout.php';
?>
