<?php
  $label = $label ?? 'Report';
  $report = $report ?? ['rows' => []];
  $generatedAt = $generatedAt ?? '';
?>
<!doctype html>
<html>
<head>
  <meta charset="utf-8">
  <style>
    body { font-family: DejaVu Sans, Arial, sans-serif; font-size: 12px; color: #0f172a; }
    h1 { font-size: 18px; margin: 0 0 4px; }
    h2 { font-size: 12px; margin: 0 0 10px; color: #475569; font-weight: normal; }
    table { width: 100%; border-collapse: collapse; margin-top: 12px; }
    th, td { border: 1px solid #e2e8f0; padding: 6px; text-align: left; }
    th { background: #f1f5f9; font-size: 11px; text-transform: uppercase; letter-spacing: 0.04em; }
    tbody tr:nth-child(even) { background: #f8fafc; }
    .note { margin-top: 8px; color: #b45309; }
    .meta { display: block; margin-top: 6px; font-size: 11px; color: #64748b; }
  </style>
</head>
<body>
  <h1><?= htmlspecialchars($label) ?></h1>
  <h2><?= htmlspecialchars($report['title'] ?? '') ?><?= !empty($report['subtitle']) ? ' • ' . htmlspecialchars($report['subtitle']) : '' ?></h2>
  <span class="meta">Generated <?= htmlspecialchars($generatedAt) ?></span>
  <?php if (!empty($report['note'])): ?>
    <div class="note"><?= htmlspecialchars($report['note']) ?></div>
  <?php endif; ?>

  <table>
    <thead>
      <tr>
        <?php foreach ($report['headers'] ?? [] as $h): ?>
          <th><?= htmlspecialchars($h) ?></th>
        <?php endforeach; ?>
      </tr>
    </thead>
    <tbody>
      <?php if (empty($report['rows'])): ?>
        <tr>
          <td colspan="<?= count($report['headers'] ?? []) ?: 1 ?>">No data.</td>
        </tr>
      <?php else: ?>
        <?php foreach ($report['rows'] as $row): ?>
          <tr>
            <?php foreach ($report['headers'] as $header): ?>
              <?php
                $cell = '';
                if ($type === 'attendance_class') {
                  $map = ['Student' => 'student', 'Present' => 'present', 'Absent' => 'absent', 'Late' => 'late', 'Excused' => 'excused', 'Unmarked' => 'unmarked'];
                  $cell = $row[$map[$header] ?? ''] ?? '';
                } elseif ($type === 'attendance_student') {
                  $map = ['Present' => 'present', 'Absent' => 'absent', 'Late' => 'late', 'Excused' => 'excused', 'Unmarked' => 'unmarked'];
                  $cell = $row[$map[$header] ?? ''] ?? '';
                } elseif ($type === 'faithbook') {
                  $map = ['Date' => 'entry_date', 'Type' => 'entry_type', 'Title' => 'title', 'Content' => 'content'];
                  $cell = $row[$map[$header] ?? ''] ?? '';
                } elseif ($type === 'training') {
                  $map = ['Teacher' => 'full_name', 'PSO' => 'pso_date', 'Formation' => 'formation_date'];
                  $cell = $row[$map[$header] ?? ''] ?? '';
                } elseif ($type === 'class_list') {
                  $map = ['Student' => 'full_name', 'DOB' => 'dob', 'Status' => 'status'];
                  $cell = $row[$map[$header] ?? ''] ?? '';
                }
              ?>
              <td><?= htmlspecialchars((string)$cell) ?></td>
            <?php endforeach; ?>
          </tr>
        <?php endforeach; ?>
      <?php endif; ?>
    </tbody>
  </table>
</body>
</html>
