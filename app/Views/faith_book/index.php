<?php
  $pageTitle = 'Faith Book';
  $pageSubtitle = 'Student faith records and notes.';

  ob_start();
?>
  <div class="flex flex-col gap-4">
    <form method="get" class="filter-bar flex flex-wrap items-end gap-3">
      <div>
        <label class="section-label">Search</label>
        <input name="q" value="<?= htmlspecialchars($q ?? '') ?>" placeholder="Student name" class="mt-1.5 rounded-xl border border-slate-200 px-3 py-2 text-sm">
      </div>
      <button class="btn btn-primary btn-sm">Search</button>
      <a href="/faith-book" class="btn btn-secondary btn-sm">Reset</a>
    </form>

    <div class="table-wrap overflow-x-auto rounded-2xl border border-slate-200">
      <table class="cdm-table w-full text-left text-sm">
        <thead class="bg-slate-50 text-xs uppercase tracking-wide text-slate-500">
          <tr>
            <th class="px-4 py-3">Student</th>
            <th class="px-4 py-3">Class</th>
            <th class="px-4 py-3">Status</th>
            <th class="px-4 py-3">Actions</th>
          </tr>
        </thead>
        <tbody>
          <?php if (empty($students)): ?>
            <tr>
              <td colspan="4" class="px-4 py-6">
                <?php $message = 'No students found.'; ?>
                <?php require __DIR__ . '/../partials/empty.php'; ?>
              </td>
            </tr>
          <?php else: ?>
            <?php foreach ($students as $student): ?>
              <tr class="border-t border-slate-200">
                <td class="px-4 py-3 font-semibold text-slate-900"><?= htmlspecialchars($student['full_name']) ?></td>
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
                <td class="px-4 py-3">
                  <?php
                    $fbBadge = match($student['status'] ?? '') {
                      'ACTIVE' => 'badge-success',
                      'INACTIVE' => 'badge-neutral',
                      'GRADUATED' => 'badge-info',
                      'TRANSFERRED' => 'badge-warning',
                      default => 'badge-neutral',
                    };
                  ?>
                  <span class="badge <?= $fbBadge ?>"><?= htmlspecialchars($student['status'] ?? '') ?></span>
                </td>
                <td class="px-4 py-3">
                  <a href="/faith-book/<?= (int)$student['id'] ?>" class="btn btn-secondary btn-xs">Open</a>
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
