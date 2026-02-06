<?php
  $pageTitle = 'Imports';
  $pageSubtitle = 'Bulk import students, teachers, and classes.';

  ob_start();
?>
  <div class="flex flex-col gap-4">
    <div class="flex items-center justify-between">
      <div class="text-sm text-slate-600">Recent import jobs</div>
      <a href="/imports/create" class="rounded-xl bg-slate-900 px-4 py-2 text-sm font-semibold text-white">New Import</a>
    </div>

    <div class="overflow-hidden rounded-2xl border border-slate-200">
      <table class="w-full text-left text-sm">
        <thead class="bg-slate-50 text-xs uppercase tracking-wide text-slate-500">
          <tr>
            <th class="px-4 py-3">Type</th>
            <th class="px-4 py-3">Status</th>
            <th class="px-4 py-3">Rows</th>
            <th class="px-4 py-3">Created</th>
            <th class="px-4 py-3">Actions</th>
          </tr>
        </thead>
        <tbody>
          <?php if (empty($jobs)): ?>
            <tr>
              <td colspan="5" class="px-4 py-6">
                <?php $message = 'No import jobs yet.'; ?>
                <?php require __DIR__ . '/../partials/empty.php'; ?>
              </td>
            </tr>
          <?php else: ?>
            <?php foreach ($jobs as $job): ?>
              <tr class="border-t border-slate-200">
                <td class="px-4 py-3 font-semibold text-slate-900"><?= htmlspecialchars($job['job_type']) ?></td>
                <td class="px-4 py-3 text-slate-600"><?= htmlspecialchars($job['status']) ?></td>
                <td class="px-4 py-3 text-slate-600"><?= (int)($job['success_rows'] ?? 0) ?> success / <?= (int)($job['failed_rows'] ?? 0) ?> failed</td>
                <td class="px-4 py-3 text-slate-600"><?= htmlspecialchars($job['created_at']) ?></td>
                <td class="px-4 py-3">
                  <a href="/imports/<?= (int)$job['id'] ?>" class="rounded-lg border border-slate-200 px-3 py-1 text-xs">View</a>
                </td>
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
