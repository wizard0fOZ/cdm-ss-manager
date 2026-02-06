<?php
  $pageTitle = 'Teacher Training';
  $pageSubtitle = 'Coordinator-managed PSO and formation records.';

  ob_start();
?>
  <div class="flex flex-col gap-4">
    <div class="flex flex-wrap items-end justify-between gap-3">
      <form method="get" class="grid gap-3 md:grid-cols-4">
        <div>
          <label class="text-xs text-slate-500">Teacher</label>
          <select name="user_id" class="mt-1 w-full rounded-xl border border-slate-200 px-3 py-2 text-sm" data-enhance="search">
            <option value="">All</option>
            <?php foreach ($users as $user): ?>
              <option value="<?= (int)$user['id'] ?>" <?= (string)$userFilter === (string)$user['id'] ? 'selected' : '' ?>><?= htmlspecialchars($user['full_name']) ?></option>
            <?php endforeach; ?>
          </select>
        </div>
        <div>
          <label class="text-xs text-slate-500">Type</label>
          <select name="type" class="mt-1 w-full rounded-xl border border-slate-200 px-3 py-2 text-sm" data-enhance="search">
            <option value="">All</option>
            <?php foreach ($types as $t): ?>
              <option value="<?= $t ?>" <?= $type === $t ? 'selected' : '' ?>><?= $t ?></option>
            <?php endforeach; ?>
          </select>
        </div>
        <div>
          <label class="text-xs text-slate-500">Search</label>
          <input name="q" value="<?= htmlspecialchars($q ?? '') ?>" placeholder="Name or title" class="mt-1 w-full rounded-xl border border-slate-200 px-3 py-2 text-sm">
        </div>
        <div class="flex items-end gap-2">
          <button class="rounded-xl bg-slate-900 px-4 py-2 text-sm font-semibold text-white">Filter</button>
          <a href="/training" class="rounded-xl border border-slate-200 px-4 py-2 text-sm">Reset</a>
        </div>
      </form>

      <a href="/training/create" class="inline-flex items-center justify-center rounded-xl bg-slate-900 px-4 py-2 text-sm font-semibold text-white">Add Record</a>
    </div>

    <div class="overflow-hidden rounded-2xl border border-slate-200">
      <table class="w-full text-left text-sm">
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
                <?php $message = 'No training records found.'; ?>
                <?php require __DIR__ . '/../partials/empty.php'; ?>
              </td>
            </tr>
          <?php else: ?>
            <?php foreach ($records as $record): ?>
              <tr class="border-t border-slate-200">
                <td class="px-4 py-3 font-semibold text-slate-900"><?= htmlspecialchars($record['full_name']) ?></td>
                <td class="px-4 py-3 text-slate-600"><?= htmlspecialchars($record['type']) ?></td>
                <td class="px-4 py-3 text-slate-600"><?= htmlspecialchars($record['title'] ?? '—') ?></td>
                <td class="px-4 py-3 text-slate-600"><?= htmlspecialchars($record['attended_date'] ?? '—') ?></td>
                <td class="px-4 py-3 text-slate-600"><?= htmlspecialchars($record['expiry_date'] ?? '—') ?></td>
                <td class="px-4 py-3">
                  <a href="/training/<?= (int)$record['id'] ?>/edit" class="rounded-lg border border-slate-200 px-3 py-1 text-xs">Edit</a>
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
