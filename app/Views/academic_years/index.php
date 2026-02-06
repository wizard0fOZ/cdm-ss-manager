<?php
  $pageTitle = 'Academic Years';
  $pageSubtitle = 'Manage academic years and active term windows.';

  ob_start();
?>
  <div class="flex items-center justify-between">
    <div class="text-sm text-slate-600">Only one academic year can be active at a time.</div>
    <a href="/academic-years/create" class="rounded-xl bg-slate-900 px-4 py-2 text-sm font-semibold text-white">Add Year</a>
  </div>

  <div class="mt-6 overflow-hidden rounded-2xl border border-slate-200">
    <table class="w-full text-left text-sm">
      <thead class="bg-slate-50 text-xs uppercase tracking-wide text-slate-500">
        <tr>
          <th class="px-4 py-3">Label</th>
          <th class="px-4 py-3">Start</th>
          <th class="px-4 py-3">End</th>
          <th class="px-4 py-3">Active</th>
          <th class="px-4 py-3">Actions</th>
        </tr>
      </thead>
      <tbody>
        <?php if (empty($years)): ?>
          <tr>
            <td colspan="5" class="px-4 py-6">
              <?php $message = 'No academic years found. Add the first academic year to begin.'; ?>
              <?php require __DIR__ . '/../partials/empty.php'; ?>
            </td>
          </tr>
        <?php else: ?>
          <?php foreach ($years as $year): ?>
            <tr class="border-t border-slate-200">
              <td class="px-4 py-3 font-semibold text-slate-900"><?= htmlspecialchars($year['label']) ?></td>
              <td class="px-4 py-3 text-slate-600"><?= htmlspecialchars($year['start_date']) ?></td>
              <td class="px-4 py-3 text-slate-600"><?= htmlspecialchars($year['end_date']) ?></td>
              <td class="px-4 py-3">
                <?php if (!empty($year['is_active'])): ?>
                  <span class="rounded-full bg-emerald-100 px-3 py-1 text-xs text-emerald-700">Active</span>
                <?php else: ?>
                  <span class="rounded-full bg-slate-100 px-3 py-1 text-xs text-slate-600">Inactive</span>
                <?php endif; ?>
              </td>
              <td class="px-4 py-3">
                <a href="/academic-years/<?= (int)$year['id'] ?>/edit" class="rounded-lg border border-slate-200 px-3 py-1 text-xs">Edit</a>
              </td>
            </tr>
          <?php endforeach; ?>
        <?php endif; ?>
      </tbody>
    </table>
  </div>
<?php
  $content = ob_get_clean();
  require __DIR__ . '/../layout.php';
?>
