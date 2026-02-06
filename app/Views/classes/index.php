<?php
  $pageTitle = 'Classes';
  $pageSubtitle = 'Manage class lists by program and session.';

  ob_start();
?>
  <div class="flex items-center justify-between">
    <div class="text-sm text-slate-600">Assign classes to academic years and sessions.</div>
    <a href="/classes/create" class="rounded-xl bg-slate-900 px-4 py-2 text-sm font-semibold text-white">Add Class</a>
  </div>

  <form id="bulk-classes-form" method="post" action="/classes/bulk">
    <div class="mt-4 flex flex-wrap items-center gap-3 rounded-2xl border border-slate-200 bg-slate-50 p-3">
      <input type="hidden" name="_csrf" value="<?= htmlspecialchars($_SESSION['_csrf'] ?? '') ?>">
      <label class="text-xs text-slate-500">Bulk Action</label>
      <select name="bulk_action" class="rounded-lg border border-slate-200 px-3 py-2 text-sm" data-enhance="search" required>
        <option value="">Select</option>
        <option value="set_status">Set Status</option>
        <option value="set_session">Set Session</option>
        <option value="set_academic_year">Set Academic Year</option>
      </select>
      <select name="status" class="rounded-lg border border-slate-200 px-3 py-2 text-sm" data-enhance="search">
        <option value="">Status</option>
        <?php foreach (['DRAFT','ACTIVE','INACTIVE'] as $opt): ?>
          <option value="<?= $opt ?>"><?= $opt ?></option>
        <?php endforeach; ?>
      </select>
      <select name="session_id" class="rounded-lg border border-slate-200 px-3 py-2 text-sm" data-enhance="search">
        <option value="">Session</option>
        <?php foreach ($sessions as $session): ?>
          <option value="<?= (int)$session['id'] ?>"><?= htmlspecialchars($session['name']) ?></option>
        <?php endforeach; ?>
      </select>
      <select name="academic_year_id" class="rounded-lg border border-slate-200 px-3 py-2 text-sm" data-enhance="search">
        <option value="">Academic Year</option>
        <?php foreach ($years as $year): ?>
          <option value="<?= (int)$year['id'] ?>"><?= htmlspecialchars($year['label']) ?></option>
        <?php endforeach; ?>
      </select>
      <button class="rounded-lg bg-slate-900 px-4 py-2 text-sm font-semibold text-white">Apply</button>
      <span class="text-xs text-slate-400">Select classes below.</span>
    </div>

    <div class="mt-4 overflow-hidden rounded-2xl border border-slate-200">
    <table class="w-full text-left text-sm">
      <thead class="bg-slate-50 text-xs uppercase tracking-wide text-slate-500">
        <tr>
          <th class="px-4 py-3">
            <input type="checkbox" data-select-all class="rounded border-slate-300">
          </th>
          <th class="px-4 py-3">Name</th>
          <th class="px-4 py-3">Program</th>
          <th class="px-4 py-3">Grade</th>
          <th class="px-4 py-3">Stream</th>
          <th class="px-4 py-3">Session</th>
          <th class="px-4 py-3">Academic Year</th>
          <th class="px-4 py-3">Status</th>
          <th class="px-4 py-3">Actions</th>
        </tr>
      </thead>
      <tbody>
          <?php if (empty($classes)): ?>
            <tr>
              <td colspan="9" class="px-4 py-6">
                <?php $message = 'No classes found. Add your first class.'; ?>
                <?php require __DIR__ . '/../partials/empty.php'; ?>
              </td>
            </tr>
          <?php else: ?>
            <?php foreach ($classes as $class): ?>
              <tr class="border-t border-slate-200">
              <td class="px-4 py-3">
                <input type="checkbox" name="ids[]" value="<?= (int)$class['id'] ?>" class="rounded border-slate-300" data-select-item>
              </td>
              <td class="px-4 py-3 font-semibold text-slate-900"><?= htmlspecialchars($class['name']) ?></td>
              <td class="px-4 py-3 text-slate-600"><?= htmlspecialchars($class['program']) ?></td>
              <td class="px-4 py-3 text-slate-600"><?= htmlspecialchars($class['grade_level'] ?? '—') ?></td>
              <td class="px-4 py-3 text-slate-600"><?= htmlspecialchars($class['stream']) ?></td>
              <td class="px-4 py-3 text-slate-600"><?= htmlspecialchars($class['session_name'] ?? '—') ?></td>
              <td class="px-4 py-3 text-slate-600"><?= htmlspecialchars($class['academic_year_label'] ?? '—') ?></td>
              <td class="px-4 py-3">
                <span class="rounded-full bg-slate-100 px-3 py-1 text-xs text-slate-600"><?= htmlspecialchars($class['status']) ?></span>
              </td>
              <td class="px-4 py-3">
                <a href="/classes/<?= (int)$class['id'] ?>/edit" class="rounded-lg border border-slate-200 px-3 py-1 text-xs">Edit</a>
              </td>
            </tr>
          <?php endforeach; ?>
        <?php endif; ?>
      </tbody>
    </table>
  </div>
  </form>
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
