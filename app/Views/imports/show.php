<?php
  $pageTitle = 'Import Details';
  $pageSubtitle = 'Review import results.';

  ob_start();
?>
  <div class="flex flex-col gap-4">
    <div class="rounded-xl border border-slate-200 bg-slate-50 p-4">
      <div class="text-sm font-semibold text-slate-900">Job #<?= (int)$job['id'] ?> · <?= htmlspecialchars($job['job_type']) ?></div>
      <div class="mt-1 text-xs text-slate-600">Status: <?= htmlspecialchars($job['status']) ?></div>
      <div class="mt-2 text-xs text-slate-600">
        Total: <?= (int)($job['total_rows'] ?? 0) ?> · Success: <?= (int)($job['success_rows'] ?? 0) ?> · Failed: <?= (int)($job['failed_rows'] ?? 0) ?>
      </div>
      <?php if (!empty($job['error_summary'])): ?>
        <div class="mt-3 rounded-lg border border-amber-200 bg-amber-50 px-3 py-2 text-xs text-amber-800 whitespace-pre-line"><?= htmlspecialchars($job['error_summary']) ?></div>
      <?php endif; ?>
    </div>

    <div class="table-wrap overflow-x-auto rounded-2xl border border-slate-200">
      <table class="cdm-table w-full text-left text-sm">
        <thead class="bg-slate-50 text-xs uppercase tracking-wide text-slate-500">
          <tr>
            <th class="px-4 py-3">Row</th>
            <th class="px-4 py-3">Status</th>
            <th class="px-4 py-3">Error</th>
          </tr>
        </thead>
        <tbody>
          <?php if (empty($rows)): ?>
            <tr>
              <td colspan="3" class="px-4 py-6">
                <?php $message = 'No rows logged.'; ?>
                <?php require __DIR__ . '/../partials/empty.php'; ?>
              </td>
            </tr>
          <?php else: ?>
            <?php foreach ($rows as $row): ?>
              <tr class="border-t border-slate-200">
                <td class="px-4 py-3 text-slate-600"><?= (int)$row['row_num'] ?></td>
                <td class="px-4 py-3 text-slate-600"><?= htmlspecialchars($row['status']) ?></td>
                <td class="px-4 py-3 text-slate-600"><?= htmlspecialchars($row['error_message'] ?? '') ?></td>
              </tr>
            <?php endforeach; ?>
          <?php endif; ?>
        </tbody>
      </table>
    </div>
  </div>
<?php
  $content = ob_get_clean();
  require __DIR__ . '/../layout.php';
?>
