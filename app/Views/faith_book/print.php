<?php
  /** @var array $student */
  /** @var array $entries */
  /** @var array $attendance */
?>
<!doctype html>
<html>
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Faith Book - <?= htmlspecialchars($student['full_name'] ?? '') ?></title>
  <style>
    body { font-family: Arial, sans-serif; color: #111827; margin: 32px; }
    h1 { font-size: 22px; margin: 0 0 6px; }
    h2 { font-size: 13px; margin: 16px 0 6px; text-transform: uppercase; letter-spacing: 0.08em; color: #6b7280; }
    .meta { font-size: 13px; color: #374151; margin-bottom: 14px; }
    .box { border: 1px solid #e5e7eb; padding: 12px; border-radius: 10px; margin-bottom: 14px; }
    .content { white-space: pre-wrap; font-size: 14px; line-height: 1.5; }
    table { width: 100%; border-collapse: collapse; font-size: 13px; }
    th, td { border: 1px solid #e5e7eb; padding: 6px 8px; text-align: left; }
    th { background: #f8fafc; }
    @media print { .no-print { display: none; } }
  </style>
</head>
<body>
  <div class="no-print" style="margin-bottom:16px;">
    <button onclick="window.print()">Print</button>
  </div>

  <h1>Faith Book</h1>
  <div class="meta">
    Name: <?= htmlspecialchars($student['full_name'] ?? '') ?> | Class: <?= htmlspecialchars($student['class_name'] ?? '—') ?> | Generated: <?= htmlspecialchars($generatedAt ?? '') ?>
  </div>

  <h2>Attendance Report (by Term)</h2>
  <div class="box">
    <?php if (empty($attendance)): ?>
      <div>No attendance data available.</div>
    <?php else: ?>
      <table>
        <thead>
          <tr>
            <th>Term</th>
            <th>Date Range</th>
            <th>Present</th>
            <th>Absent</th>
            <th>Late</th>
            <th>Excused</th>
          </tr>
        </thead>
        <tbody>
          <?php foreach ($attendance as $term): ?>
            <tr>
              <td><?= htmlspecialchars($term['term']) ?></td>
              <td><?= htmlspecialchars($term['start_date']) ?> to <?= htmlspecialchars($term['end_date']) ?></td>
              <td><?= (int)$term['counts']['PRESENT'] ?></td>
              <td><?= (int)$term['counts']['ABSENT'] ?></td>
              <td><?= (int)$term['counts']['LATE'] ?></td>
              <td><?= (int)$term['counts']['EXCUSED'] ?></td>
            </tr>
          <?php endforeach; ?>
        </tbody>
      </table>
    <?php endif; ?>
  </div>

  <h2>Faith Notes</h2>
  <?php if (empty($entries)): ?>
    <div class="box">No faith book entries.</div>
  <?php else: ?>
    <?php foreach ($entries as $entry): ?>
      <div class="box">
        <strong><?= htmlspecialchars($entry['title']) ?></strong><br>
        <span><?= htmlspecialchars($entry['entry_date']) ?> • <?= htmlspecialchars($entry['entry_type']) ?></span>
        <div class="content" style="margin-top:8px;"><?= htmlspecialchars($entry['content']) ?></div>
      </div>
    <?php endforeach; ?>
  <?php endif; ?>
</body>
</html>
