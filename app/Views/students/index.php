<?php
  $pageTitle = 'Students';
  $pageSubtitle = 'Search and manage student records.';

  ob_start();
?>
  <div class="flex flex-col gap-4">
    <div class="flex flex-col gap-3 lg:flex-row lg:items-end lg:justify-between">
      <form method="get" class="grid gap-3 md:grid-cols-4">
        <div>
          <label class="text-xs text-slate-500">Search</label>
          <input name="q" value="<?= htmlspecialchars($search ?? '') ?>" placeholder="Name or ID" class="mt-1 w-full rounded-xl border border-slate-200 px-3 py-2 text-sm">
        </div>
        <div>
          <label class="text-xs text-slate-500">Status</label>
          <select name="status" class="mt-1 w-full rounded-xl border border-slate-200 px-3 py-2 text-sm">
            <option value="">All</option>
            <?php foreach (['ACTIVE','INACTIVE','GRADUATED','TRANSFERRED'] as $opt): ?>
              <option value="<?= $opt ?>" <?= ($status ?? '') === $opt ? 'selected' : '' ?>><?= $opt ?></option>
            <?php endforeach; ?>
          </select>
        </div>
        <div>
          <label class="text-xs text-slate-500">Class</label>
          <select name="class_id" class="mt-1 w-full rounded-xl border border-slate-200 px-3 py-2 text-sm">
            <option value="">All</option>
            <?php foreach ($classes as $class): ?>
              <option value="<?= (int)$class['id'] ?>" <?= (string)($classId ?? '') === (string)$class['id'] ? 'selected' : '' ?>>
                <?= htmlspecialchars($class['name']) ?>
              </option>
            <?php endforeach; ?>
          </select>
        </div>
        <div class="flex items-end gap-2">
          <button class="rounded-xl bg-slate-900 px-4 py-2 text-sm font-semibold text-white">Filter</button>
          <a href="/students" class="rounded-xl border border-slate-200 px-4 py-2 text-sm">Reset</a>
        </div>
      </form>

      <a href="/students/create" class="inline-flex items-center justify-center rounded-xl bg-slate-900 px-4 py-2 text-sm font-semibold text-white">
        Add Student
      </a>
    </div>

    <form id="bulk-students-form" method="post" action="/students/bulk">
      <div class="flex flex-wrap items-center gap-3 rounded-2xl border border-slate-200 bg-slate-50 p-3">
        <input type="hidden" name="_csrf" value="<?= htmlspecialchars($_SESSION['_csrf'] ?? '') ?>">
        <label class="text-xs text-slate-500">Bulk Action</label>
        <select name="bulk_action" class="rounded-lg border border-slate-200 px-3 py-2 text-sm" required>
          <option value="">Select</option>
          <option value="set_status">Set Status</option>
          <option value="assign_class">Assign Class</option>
        </select>
        <select name="status" class="rounded-lg border border-slate-200 px-3 py-2 text-sm">
          <option value="">Status</option>
          <?php foreach (['ACTIVE','INACTIVE','GRADUATED','TRANSFERRED'] as $opt): ?>
            <option value="<?= $opt ?>"><?= $opt ?></option>
          <?php endforeach; ?>
        </select>
        <select name="class_id" class="rounded-lg border border-slate-200 px-3 py-2 text-sm">
          <option value="">Class</option>
          <?php foreach ($classes as $class): ?>
            <option value="<?= (int)$class['id'] ?>"><?= htmlspecialchars($class['name']) ?></option>
          <?php endforeach; ?>
        </select>
        <button class="rounded-lg bg-slate-900 px-4 py-2 text-sm font-semibold text-white">Apply</button>
        <span class="text-xs text-slate-400">Select students below.</span>
      </div>

      <div class="overflow-hidden rounded-2xl border border-slate-200">
      <table class="w-full text-left text-sm">
        <thead class="bg-slate-50 text-xs uppercase tracking-wide text-slate-500">
          <tr>
            <th class="px-4 py-3">
              <input type="checkbox" data-select-all class="rounded border-slate-300">
            </th>
            <th class="px-4 py-3">Student</th>
            <th class="px-4 py-3">Class</th>
            <th class="px-4 py-3">Status</th>
            <th class="px-4 py-3">DOB</th>
            <th class="px-4 py-3">Age</th>
            <th class="px-4 py-3">Actions</th>
          </tr>
        </thead>
        <tbody>
          <?php if (empty($students)): ?>
            <tr>
              <td colspan="7" class="px-4 py-6">
                <?php $message = 'No students found. Try adjusting filters or add a new student.'; ?>
                <?php require __DIR__ . '/../partials/empty.php'; ?>
              </td>
            </tr>
          <?php else: ?>
            <?php foreach ($students as $student): ?>
              <tr class="border-t border-slate-200">
                <td class="px-4 py-3">
                  <input type="checkbox" name="ids[]" value="<?= (int)$student['id'] ?>" class="rounded border-slate-300" data-select-item>
                </td>
                <td class="px-4 py-3">
                  <div class="font-semibold text-slate-900"><?= htmlspecialchars($student['full_name'] ?? '') ?></div>
                  <div class="flex flex-wrap gap-2 text-xs text-slate-500">
                    <span>ID: <?= htmlspecialchars($student['identity_number'] ?? '—') ?></span>
                    <?php if (!empty($student['is_rcic'])): ?>
                      <span class="rounded-full bg-amber-100 px-2 py-0.5 text-[10px] uppercase tracking-wide text-amber-700">RCIC</span>
                    <?php endif; ?>
                  </div>
                </td>
                <td class="px-4 py-3 text-slate-600"><?= htmlspecialchars($student['class_name'] ?? '—') ?></td>
                <td class="px-4 py-3">
                  <span class="rounded-full bg-slate-100 px-3 py-1 text-xs text-slate-600"><?= htmlspecialchars($student['status'] ?? '') ?></span>
                </td>
                <td class="px-4 py-3 text-slate-600"><?= htmlspecialchars($student['dob_display'] ?? $student['dob'] ?? '—') ?></td>
                <td class="px-4 py-3 text-slate-600"><?= htmlspecialchars(($student['age_display'] ?? null) !== null ? (string)$student['age_display'] : '—') ?></td>
                <td class="px-4 py-3">
                  <div class="flex items-center gap-2 text-xs">
                    <a href="/students/<?= (int)$student['id'] ?>" class="rounded-lg border border-slate-200 px-3 py-1">View</a>
                    <a href="/students/<?= (int)$student['id'] ?>/edit" class="rounded-lg border border-slate-200 px-3 py-1">Edit</a>
                  </div>
                </td>
              </tr>
            <?php endforeach; ?>
          <?php endif; ?>
        </tbody>
      </table>
      </div>
    </form>
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
