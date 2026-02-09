<?php
  $pageTitle = 'Imports';
  $pageSubtitle = 'Bulk import students, teachers, and classes.';

  ob_start();
?>
  <div class="flex flex-col gap-4">
    <div class="flex items-center justify-between">
      <div class="section-label">Recent import jobs</div>
      <a href="/imports/create" class="btn btn-primary btn-sm">
        <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/><polyline points="17 8 12 3 7 8"/><line x1="12" y1="3" x2="12" y2="15"/></svg>
        New Import
      </a>
    </div>

    <div class="table-wrap overflow-x-auto rounded-2xl border border-slate-200">
      <table class="cdm-table w-full text-left text-sm">
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
                <?php
                  $message = 'No import jobs yet.';
                  $actionLabel = 'New Import';
                  $actionHref = '/imports/create';
                ?>
                <?php require __DIR__ . '/../partials/empty.php'; ?>
              </td>
            </tr>
          <?php else: ?>
            <?php foreach ($jobs as $job): ?>
              <tr class="border-t border-slate-200">
                <td class="px-4 py-3 font-semibold text-slate-900"><?= htmlspecialchars($job['job_type']) ?></td>
                <td class="px-4 py-3">
                  <?php
                    $importBadge = match($job['status'] ?? '') {
                      'COMPLETED' => 'badge-success',
                      'FAILED' => 'badge-danger',
                      'PROCESSING' => 'badge-info',
                      'PENDING' => 'badge-warning',
                      default => 'badge-neutral',
                    };
                  ?>
                  <span class="badge <?= $importBadge ?>"><?= htmlspecialchars($job['status']) ?></span>
                </td>
                <td class="px-4 py-3 text-slate-600">
                  <span class="text-emerald-600"><?= (int)($job['success_rows'] ?? 0) ?></span> /
                  <span class="<?= (int)($job['failed_rows'] ?? 0) > 0 ? 'text-red-500' : 'text-slate-400' ?>"><?= (int)($job['failed_rows'] ?? 0) ?> failed</span>
                </td>
                <td class="px-4 py-3 text-slate-600"><?= htmlspecialchars($job['created_at']) ?></td>
                <td class="px-4 py-3">
                  <a href="/imports/<?= (int)$job['id'] ?>" class="btn btn-secondary btn-xs">View</a>
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
