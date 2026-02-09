<?php
  $pageTitle = 'Admin • Monitoring';
  $pageSubtitle = 'Recent imports and audit events.';

  ob_start();
?>
  <?php require __DIR__ . '/../_nav.php'; ?>

  <div class="grid gap-6">
    <div>
      <div class="text-sm font-semibold text-slate-900 mb-2">Recent Import Jobs</div>
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
            <?php if (empty($imports)): ?>
              <tr>
                <td colspan="5" class="px-4 py-6">
                  <?php $message = 'No import jobs found.'; ?>
                  <?php require __DIR__ . '/../../partials/empty.php'; ?>
                </td>
              </tr>
            <?php else: ?>
              <?php foreach ($imports as $job): ?>
                <tr class="border-t border-slate-200">
                  <td class="px-4 py-3 font-semibold text-slate-900"><?= htmlspecialchars($job['job_type']) ?></td>
                  <td class="px-4 py-3 text-slate-600"><?= htmlspecialchars($job['status']) ?></td>
                  <td class="px-4 py-3 text-slate-600"><?= (int)($job['success_rows'] ?? 0) ?> / <?= (int)($job['failed_rows'] ?? 0) ?></td>
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

    <div>
      <div class="flex flex-wrap items-center justify-between gap-3 mb-2">
        <div class="text-sm font-semibold text-slate-900">Audit Log</div>
        <form method="get" action="/admin/monitoring" class="flex flex-wrap items-end gap-2 text-xs text-slate-600">
          <label class="grid gap-1">
            <span class="text-xs text-slate-500">Actor (user id)</span>
            <input name="actor" value="<?= htmlspecialchars($auditActor ?? '') ?>" class="w-28 rounded-lg border border-slate-200 px-2 py-1 text-xs" placeholder="e.g. 5">
          </label>
          <label class="grid gap-1">
            <span class="text-xs text-slate-500">Entity</span>
            <input name="entity" value="<?= htmlspecialchars($auditEntity ?? '') ?>" class="w-32 rounded-lg border border-slate-200 px-2 py-1 text-xs" placeholder="e.g. student">
          </label>
          <label class="grid gap-1">
            <span class="text-xs text-slate-500">From</span>
            <input type="date" name="start" value="<?= htmlspecialchars($auditStart ?? '') ?>" class="rounded-lg border border-slate-200 px-2 py-1 text-xs">
          </label>
          <label class="grid gap-1">
            <span class="text-xs text-slate-500">To</span>
            <input type="date" name="end" value="<?= htmlspecialchars($auditEnd ?? '') ?>" class="rounded-lg border border-slate-200 px-2 py-1 text-xs">
          </label>
          <button class="btn btn-secondary btn-xs">Filter</button>
          <a href="/admin/monitoring" class="btn btn-secondary btn-xs">Reset</a>
        </form>
      </div>
      <div class="table-wrap overflow-x-auto rounded-2xl border border-slate-200">
        <table class="cdm-table w-full text-left text-sm">
          <thead class="bg-slate-50 text-xs uppercase tracking-wide text-slate-500">
            <tr>
              <th class="px-4 py-3">Action</th>
              <th class="px-4 py-3">Entity</th>
              <th class="px-4 py-3">Actor</th>
              <th class="px-4 py-3">Time</th>
              <th class="px-4 py-3">Actions</th>
            </tr>
          </thead>
          <tbody>
            <?php if (empty($audits)): ?>
              <tr>
                <td colspan="5" class="px-4 py-6">
                  <?php $message = 'No audit records.'; ?>
                  <?php require __DIR__ . '/../../partials/empty.php'; ?>
                </td>
              </tr>
            <?php else: ?>
              <?php foreach ($audits as $audit): ?>
                <tr class="border-t border-slate-200">
                  <td class="px-4 py-3 text-slate-600"><?= htmlspecialchars($audit['action'] ?? '') ?></td>
                  <td class="px-4 py-3 text-slate-600"><?= htmlspecialchars(($audit['entity_type'] ?? '') . ' #' . ($audit['entity_id'] ?? '')) ?></td>
                  <td class="px-4 py-3 text-slate-600"><?= htmlspecialchars((string)($audit['actor_user_id'] ?? '')) ?></td>
                  <td class="px-4 py-3 text-slate-600"><?= htmlspecialchars($audit['created_at'] ?? '') ?></td>
                  <td class="px-4 py-3">
                    <a href="/admin/audits/<?= (int)$audit['id'] ?>" class="btn btn-secondary btn-xs">Details</a>
                  </td>
                </tr>
              <?php endforeach; ?>
            <?php endif; ?>
          </tbody>
        </table>
      </div>
    </div>
  </div>
<?php
  $content = ob_get_clean();
  require __DIR__ . '/../../layout.php';
?>
