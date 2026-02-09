<div id="students-table" class="table-wrap overflow-x-auto rounded-2xl border border-slate-200">
  <table class="cdm-table w-full text-left text-sm">
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
            <?php
              $message = 'No students found. Try adjusting filters or add a new student.';
              $actionLabel = 'Add Student';
              $actionHref = '/students/create';
            ?>
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
                <?php if (!empty($student['docs_missing'])): ?>
                  <span class="rounded-full bg-rose-100 px-2 py-0.5 text-[10px] uppercase tracking-wide text-rose-700">
                    Docs missing: <?= htmlspecialchars(implode(', ', $student['docs_missing'])) ?>
                  </span>
                <?php endif; ?>
              </div>
            </td>
            <td class="px-4 py-3 text-slate-600">
              <div><?= htmlspecialchars($student['class_name'] ?? '—') ?></div>
              <?php
                $teacherRows = $classTeachers[(int)($student['class_id'] ?? 0)] ?? [];
                if ($teacherRows) {
                  $labels = [];
                  foreach ($teacherRows as $row) {
                    $role = ($row['assignment_role'] ?? '') === 'MAIN' ? 'Main' : 'Asst';
                    $labels[] = htmlspecialchars($row['full_name']) . ' (' . $role . ')';
                  }
                  echo '<div class="mt-1 text-xs text-slate-500">' . implode(', ', $labels) . '</div>';
                }
              ?>
            </td>
            <?php require __DIR__ . '/_status_cell.php'; ?>
            <td class="px-4 py-3 text-slate-600"><?= htmlspecialchars($student['dob_display'] ?? $student['dob'] ?? '—') ?></td>
            <td class="px-4 py-3 text-slate-600"><?= htmlspecialchars(($student['age_display'] ?? null) !== null ? (string)$student['age_display'] : '—') ?></td>
            <td class="px-4 py-3">
              <div class="flex items-center gap-2 text-xs">
                <a href="/students/<?= (int)$student['id'] ?>" class="btn btn-secondary btn-xs">View</a>
                <a href="/students/<?= (int)$student['id'] ?>/edit" class="btn btn-secondary btn-xs">Edit</a>
              </div>
            </td>
          </tr>
        <?php endforeach; ?>
      <?php endif; ?>
    </tbody>
  </table>
</div>

<?php
  $pagination = $pagination ?? null;
  if ($pagination && $pagination['totalPages'] > 1):
    $base = $query ? ('?' . $query . '&') : '?';
?>
  <div class="mt-3 flex items-center justify-between text-sm">
    <div class="text-slate-500">
      Page <?= (int)$pagination['page'] ?> of <?= (int)$pagination['totalPages'] ?> • <?= (int)$pagination['total'] ?> students
    </div>
    <div class="flex items-center gap-2">
      <?php if ($pagination['hasPrev']): ?>
        <a class="btn btn-secondary btn-xs"
           href="/students<?= $base ?>page=<?= (int)($pagination['page'] - 1) ?>"
           hx-get="/students/partial<?= $base ?>page=<?= (int)($pagination['page'] - 1) ?>"
           hx-target="#students-table"
           hx-swap="outerHTML"
           hx-push-url="true">Prev</a>
      <?php else: ?>
        <span class="btn btn-secondary btn-xs text-slate-300">Prev</span>
      <?php endif; ?>
      <?php if ($pagination['hasNext']): ?>
        <a class="btn btn-secondary btn-xs"
           href="/students<?= $base ?>page=<?= (int)($pagination['page'] + 1) ?>"
           hx-get="/students/partial<?= $base ?>page=<?= (int)($pagination['page'] + 1) ?>"
           hx-target="#students-table"
           hx-swap="outerHTML"
           hx-push-url="true">Next</a>
      <?php else: ?>
        <span class="btn btn-secondary btn-xs text-slate-300">Next</span>
      <?php endif; ?>
    </div>
  </div>
<?php endif; ?>
