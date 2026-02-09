<?php
  $pageTitle = 'Teacher Training';
  $pageSubtitle = 'Coordinator-managed PSO and formation records.';

  ob_start();
?>
  <div class="flex flex-col gap-4">
    <div class="flex flex-col gap-3 lg:flex-row lg:items-end lg:justify-between">
      <form method="get" class="filter-bar grid flex-1 gap-3 md:grid-cols-4">
        <div>
          <label class="section-label">Teacher</label>
          <select name="user_id" class="mt-1.5 w-full rounded-xl border border-slate-200 px-3 py-2 text-sm" data-enhance="search">
            <option value="">All</option>
            <?php foreach ($users as $user): ?>
              <option value="<?= (int)$user['id'] ?>" <?= (string)$userFilter === (string)$user['id'] ? 'selected' : '' ?>><?= htmlspecialchars($user['full_name']) ?></option>
            <?php endforeach; ?>
          </select>
        </div>
        <div>
          <label class="section-label">Type</label>
          <select name="type" class="mt-1.5 w-full rounded-xl border border-slate-200 px-3 py-2 text-sm" data-enhance="search">
            <option value="">All</option>
            <?php foreach ($types as $t): ?>
              <option value="<?= $t ?>" <?= $type === $t ? 'selected' : '' ?>><?= $t ?></option>
            <?php endforeach; ?>
          </select>
        </div>
        <div>
          <label class="section-label">Search</label>
          <input name="q" value="<?= htmlspecialchars($q ?? '') ?>" placeholder="Name or title" class="mt-1.5 w-full rounded-xl border border-slate-200 px-3 py-2 text-sm">
        </div>
        <div class="flex items-end gap-2">
          <button class="btn btn-primary btn-sm">Filter</button>
          <a href="/training" class="btn btn-secondary btn-sm">Reset</a>
        </div>
      </form>

      <a href="/training/create" class="btn btn-primary btn-sm">
        <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/></svg>
        Add Record
      </a>
    </div>

    <div class="table-wrap overflow-x-auto rounded-2xl border border-slate-200">
      <table class="cdm-table w-full text-left text-sm">
        <thead class="bg-slate-50 text-xs uppercase tracking-wide text-slate-500">
          <tr>
            <th class="px-4 py-3">Teacher</th>
            <th class="px-4 py-3">Type</th>
            <th class="px-4 py-3">Title</th>
            <th class="px-4 py-3">Attended</th>
            <th class="px-4 py-3">Expiry</th>
            <th class="px-4 py-3">Actions</th>
          </tr>
        </thead>
        <tbody>
          <?php if (empty($records)): ?>
            <tr>
              <td colspan="6" class="px-4 py-6">
                <?php
                  $message = 'No training records found.';
                  $actionLabel = 'Add Record';
                  $actionHref = '/training/create';
                ?>
                <?php require __DIR__ . '/../partials/empty.php'; ?>
              </td>
            </tr>
          <?php else: ?>
            <?php foreach ($records as $record): ?>
              <tr class="border-t border-slate-200">
                <td class="px-4 py-3 font-semibold text-slate-900"><?= htmlspecialchars($record['full_name']) ?></td>
                <td class="px-4 py-3"><span class="badge badge-neutral"><?= htmlspecialchars($record['type']) ?></span></td>
                <td class="px-4 py-3 text-slate-600"><?= htmlspecialchars($record['title'] ?? '—') ?></td>
                <td class="px-4 py-3 text-slate-600"><?= htmlspecialchars($record['attended_date'] ?? '—') ?></td>
                <td class="px-4 py-3 text-slate-600"><?= htmlspecialchars($record['expiry_date'] ?? '—') ?></td>
                <td class="px-4 py-3">
                  <a href="/training/<?= (int)$record['id'] ?>/edit" class="btn btn-secondary btn-xs">Edit</a>
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
