<?php
  $pageTitle = 'Academic Years';
  $pageSubtitle = 'Manage academic years and active term windows.';

  ob_start();
?>
  <div class="flex items-center justify-between">
    <div class="text-sm text-slate-600">Only one academic year can be active at a time.</div>
    <a href="/academic-years/create" class="rounded-xl bg-slate-900 px-4 py-2 text-sm font-semibold text-white">Add Year</a>
  </div>

  <form id="bulk-years-form" method="post" action="/academic-years/bulk" class="mt-4 flex flex-wrap items-center gap-3 rounded-2xl border border-slate-200 bg-slate-50 p-3">
    <input type="hidden" name="_csrf" value="<?= htmlspecialchars($_SESSION['_csrf'] ?? '') ?>">
    <label class="text-xs text-slate-500">Bulk Action</label>
    <select name="bulk_action" class="rounded-lg border border-slate-200 px-3 py-2 text-sm" required>
      <option value="">Select</option>
      <option value="set_active">Set Active (one)</option>
    </select>
    <button class="rounded-lg bg-slate-900 px-4 py-2 text-sm font-semibold text-white">Apply</button>
    <span class="text-xs text-slate-400">Select one year below.</span>
  </form>

  <div class="mt-4 overflow-hidden rounded-2xl border border-slate-200">
    <table class="w-full text-left text-sm">
      <thead class="bg-slate-50 text-xs uppercase tracking-wide text-slate-500">
        <tr>
          <th class="px-4 py-3">
            <input type="checkbox" data-select-all class="rounded border-slate-300">
          </th>
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
            <td colspan="6" class="px-4 py-6">
              <?php $message = 'No academic years found. Add the first academic year to begin.'; ?>
              <?php require __DIR__ . '/../partials/empty.php'; ?>
            </td>
          </tr>
        <?php else: ?>
          <?php foreach ($years as $year): ?>
            <tr class="border-t border-slate-200">
              <td class="px-4 py-3">
                <input type="checkbox" name="ids[]" value="<?= (int)$year['id'] ?>" class="rounded border-slate-300" form="bulk-years-form" data-select-item>
              </td>
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
  <script>
    (function () {
      const selectAll = document.querySelector('[data-select-all]');
      const items = document.querySelectorAll('[data-select-item]');
      if (!selectAll) return;
      selectAll.addEventListener('change', () => {
        items.forEach((item) => { item.checked = selectAll.checked; });
      });
    })();
  </script>
<?php
  $content = ob_get_clean();
  require __DIR__ . '/../layout.php';
?>
