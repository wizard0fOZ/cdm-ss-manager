<?php
  $pageTitle = 'Academic Years';
  $pageSubtitle = 'Manage academic years and active term windows.';

  ob_start();
?>
  <div class="flex items-center justify-between">
    <div class="section-label">Only one academic year can be active at a time.</div>
    <a href="/academic-years/create" class="btn btn-primary btn-sm">
      <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/></svg>
      Add Year
    </a>
  </div>

  <form id="bulk-years-form" method="post" action="/academic-years/bulk" class="filter-bar mt-4 flex flex-wrap items-center gap-3">
    <input type="hidden" name="_csrf" value="<?= htmlspecialchars($_SESSION['_csrf'] ?? '') ?>">
    <label class="section-label">Bulk Action</label>
    <select name="bulk_action" class="rounded-lg border border-slate-200 px-3 py-2 text-sm" data-enhance="search" required>
      <option value="">Select</option>
      <option value="set_active">Set Active (one)</option>
    </select>
    <button class="btn btn-primary btn-sm"
            data-confirm
            data-confirm-title="Set Active Year"
            data-confirm-message="This will set the selected year as active and deactivate others. Continue?"
            data-confirm-text="Set Active"
            data-confirm-form="bulk-years-form">Apply</button>
    <span class="text-xs text-slate-400">Select one year below.</span>
  </form>

  <div class="mt-4 table-wrap overflow-x-auto rounded-2xl border border-slate-200">
    <table class="cdm-table w-full text-left text-sm">
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
              <?php
                $message = 'No academic years found. Add the first academic year to begin.';
                $actionLabel = 'Add Year';
                $actionHref = '/academic-years/create';
              ?>
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
                  <span class="badge badge-success">Active</span>
                <?php else: ?>
                  <span class="badge badge-neutral">Inactive</span>
                <?php endif; ?>
              </td>
              <td class="px-4 py-3">
                <a href="/academic-years/<?= (int)$year['id'] ?>/edit" class="btn btn-secondary btn-xs">Edit</a>
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
