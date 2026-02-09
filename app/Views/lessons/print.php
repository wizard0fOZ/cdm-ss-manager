<?php
  /** @var array $lesson */
?>
<!doctype html>
<html>
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Lesson Plan - <?= htmlspecialchars($lesson['title'] ?? '') ?></title>
  <link rel="stylesheet" href="/assets/app.css">
  <style>
    body { font-family: Arial, sans-serif; color: #111827; margin: 32px; }
    h1 { font-size: 22px; margin: 0 0 6px; }
    h2 { font-size: 14px; margin: 18px 0 6px; text-transform: uppercase; letter-spacing: 0.08em; color: #6b7280; }
    .meta { font-size: 13px; color: #374151; margin-bottom: 14px; }
    .box { border: 1px solid #e5e7eb; padding: 12px; border-radius: 10px; margin-bottom: 14px; }
    .content { white-space: pre-wrap; font-size: 14px; line-height: 1.5; }
    @media print { .no-print { display: none; } }
  </style>
</head>
<body>
  <div class="no-print" style="margin-bottom:16px;">
    <button class="btn btn-secondary" onclick="window.print()">Print</button>
  </div>

  <h1><?= htmlspecialchars($lesson['title'] ?? '') ?></h1>
  <div class="meta">
    Class: <?= htmlspecialchars($lesson['class_name'] ?? '') ?> | Session: <?= htmlspecialchars($lesson['session_name'] ?? '—') ?> | Date: <?= htmlspecialchars($lesson['session_date'] ?? '') ?>
  </div>

  <?php if (!empty($lesson['description'])): ?>
    <h2>Summary</h2>
    <div class="box content"><?= htmlspecialchars($lesson['description']) ?></div>
  <?php endif; ?>

  <h2>Lesson Content</h2>
  <div class="box content"><?= htmlspecialchars($lesson['content'] ?? '') ?></div>

  <?php if (!empty($lesson['url'])): ?>
    <h2>Resources</h2>
    <div class="box"><a href="<?= htmlspecialchars($lesson['url']) ?>"><?= htmlspecialchars($lesson['url']) ?></a></div>
  <?php endif; ?>
</body>
</html>
