<?php
  $pageTitle = 'Classes';
  $pageSubtitle = 'Manage class lists by program and session.';

  ob_start();
?>
  <div class="flex items-center justify-between">
    <div class="section-label">Assign classes to academic years and sessions.</div>
    <a href="/classes/create" class="btn btn-primary btn-sm">
      <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/></svg>
      Add Class
    </a>
  </div>

  <form method="get" class="filter-bar mt-4 flex flex-wrap items-end gap-3">
    <div>
      <label class="section-label">Teacher</label>
      <select name="teacher_id" class="mt-1.5 w-full rounded-xl border border-slate-200 px-3 py-2 text-sm" data-enhance="search">
        <option value="">All teachers</option>
        <?php foreach ($teachers as $teacher): ?>
          <option value="<?= (int)$teacher['id'] ?>" <?= (int)($teacherId ?? 0) === (int)$teacher['id'] ? 'selected' : '' ?>>
            <?= htmlspecialchars($teacher['full_name']) ?>
          </option>
        <?php endforeach; ?>
      </select>
    </div>
    <div class="flex items-end gap-2">
      <button class="btn btn-primary btn-sm">Filter</button>
      <a href="/classes" class="btn btn-secondary btn-sm">Reset</a>
    </div>
  </form>

  <form id="bulk-classes-form" method="post" action="/classes/bulk">
    <div class="filter-bar mt-4 flex flex-wrap items-center gap-3">
      <input type="hidden" name="_csrf" value="<?= htmlspecialchars($_SESSION['_csrf'] ?? '') ?>">
      <label class="section-label">Bulk Action</label>
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
      <button class="btn btn-primary btn-sm"
              data-confirm
              data-confirm-title="Apply Bulk Action"
              data-confirm-message="This will apply the selected action to the chosen classes. Continue?"
              data-confirm-text="Apply"
              data-confirm-form="bulk-classes-form">Apply</button>
      <span class="text-xs text-slate-400">Select classes below.</span>
    </div>

    <div class="mt-4 table-wrap overflow-x-auto rounded-2xl border border-slate-200">
    <table class="cdm-table w-full text-left text-sm">
      <thead class="bg-slate-50 text-xs uppercase tracking-wide text-slate-500">
        <tr>
          <th class="px-4 py-3">
            <input type="checkbox" data-select-all class="rounded border-slate-300">
          </th>
          <th class="px-4 py-3">Name</th>
          <th class="px-4 py-3">Program</th>
          <th class="px-4 py-3">Grade</th>
          <th class="px-4 py-3">Stream</th>
          <th class="px-4 py-3">Teachers</th>
          <th class="px-4 py-3">Session</th>
          <th class="px-4 py-3">Academic Year</th>
          <th class="px-4 py-3">Status</th>
          <th class="px-4 py-3">Actions</th>
        </tr>
      </thead>
      <tbody>
          <?php if (empty($classes)): ?>
            <tr>
              <td colspan="10" class="px-4 py-6">
                <?php
                  $message = 'No classes found. Add your first class.';
                  $actionLabel = 'Add Class';
                  $actionHref = '/classes/create';
                ?>
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
              <td class="px-4 py-3 text-slate-600">
                <?php
                  $rows = $assignments[$class['id']] ?? [];
                  if (!$rows) {
                    echo '—';
                  } else {
                    $labels = [];
                    foreach ($rows as $row) {
                      $roleLabel = ($row['assignment_role'] ?? '') === 'MAIN' ? 'Main' : 'Asst';
                      $labels[] = htmlspecialchars($row['full_name']) . ' (' . $roleLabel . ')';
                    }
                    echo implode(', ', $labels);
                  }
                ?>
              </td>
              <td class="px-4 py-3 text-slate-600"><?= htmlspecialchars($class['session_name'] ?? '—') ?></td>
              <td class="px-4 py-3 text-slate-600"><?= htmlspecialchars($class['academic_year_label'] ?? '—') ?></td>
              <td class="px-4 py-3">
                <?php
                  $classBadge = match($class['status'] ?? '') {
                    'ACTIVE' => 'badge-success',
                    'DRAFT' => 'badge-neutral',
                    'INACTIVE' => 'badge-warning',
                    default => 'badge-neutral',
                  };
                ?>
                <span class="badge <?= $classBadge ?>"><?= htmlspecialchars($class['status']) ?></span>
              </td>
              <td class="px-4 py-3">
                <a href="/classes/<?= (int)$class['id'] ?>/edit" class="btn btn-secondary btn-xs">Edit</a>
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
