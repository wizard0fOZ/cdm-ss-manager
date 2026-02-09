<?php
  $pageTitle = 'Attendance';
  $pageSubtitle = 'Select a class to mark attendance.';

  ob_start();
?>
  <div class="flex flex-col gap-4">
    <form method="get" class="filter-bar flex flex-wrap items-end gap-3">
      <div>
        <label class="section-label">Date</label>
        <input type="date" name="date" value="<?= htmlspecialchars($date) ?>" class="mt-1.5 rounded-xl border border-slate-200 px-3 py-2 text-sm">
      </div>
      <div>
        <label class="section-label">Session</label>
        <select name="session_id" class="mt-1.5 rounded-xl border border-slate-200 px-3 py-2 text-sm" data-enhance="search">
          <option value="">All</option>
          <?php foreach ($sessions as $session): ?>
            <option value="<?= (int)$session['id'] ?>" <?= (string)$sessionId === (string)$session['id'] ? 'selected' : '' ?>><?= htmlspecialchars($session['name']) ?></option>
          <?php endforeach; ?>
        </select>
      </div>
      <div>
        <label class="section-label">Program</label>
        <select name="program" class="mt-1.5 rounded-xl border border-slate-200 px-3 py-2 text-sm" data-enhance="search">
          <option value="">All</option>
          <?php foreach ($programs as $p): ?>
            <option value="<?= htmlspecialchars($p['program']) ?>" <?= ($program ?? '') === $p['program'] ? 'selected' : '' ?>><?= htmlspecialchars($p['program']) ?></option>
          <?php endforeach; ?>
        </select>
      </div>
      <div>
        <label class="section-label">Class</label>
        <select name="class_id" class="mt-1.5 rounded-xl border border-slate-200 px-3 py-2 text-sm" data-enhance="search">
          <option value="">All</option>
          <?php foreach ($classes as $class): ?>
            <option value="<?= (int)$class['id'] ?>" <?= (string)$classId === (string)$class['id'] ? 'selected' : '' ?>><?= htmlspecialchars($class['name']) ?></option>
          <?php endforeach; ?>
        </select>
      </div>
      <div class="flex items-end gap-2">
        <button class="btn btn-primary btn-sm">Load</button>
        <a href="/attendance" class="btn btn-secondary btn-sm">Reset</a>
      </div>
      <span class="text-xs text-slate-400">Sunday locks at 11:59pm.</span>
    </form>

    <div class="table-wrap overflow-x-auto rounded-2xl border border-slate-200">
      <table class="cdm-table w-full text-left text-sm">
        <thead class="bg-slate-50 text-xs uppercase tracking-wide text-slate-500">
          <tr>
            <th class="px-4 py-3">Class</th>
            <th class="px-4 py-3">Session</th>
            <th class="px-4 py-3">Teachers</th>
            <th class="px-4 py-3">Year</th>
            <th class="px-4 py-3">Status</th>
            <th class="px-4 py-3">Actions</th>
          </tr>
        </thead>
        <tbody>
          <?php if (empty($classes)): ?>
            <tr>
              <td colspan="6" class="px-4 py-6">
                <?php $message = 'No classes available for attendance.'; ?>
                <?php require __DIR__ . '/../partials/empty.php'; ?>
              </td>
            </tr>
          <?php else: ?>
            <?php
              $cleanLabel = function ($value) {
                $value = trim((string)$value);
                $value = preg_replace('/_+/', ' ', $value);
                return trim($value, " _");
              };
            ?>
            <?php foreach ($classes as $class): ?>
              <tr class="border-t border-slate-200">
                <td class="px-4 py-3 font-semibold text-slate-900"><?= htmlspecialchars($cleanLabel($class['name'])) ?></td>
                <td class="px-4 py-3 text-slate-600"><?= htmlspecialchars($class['session_name'] ?? '—') ?></td>
                <td class="px-4 py-3 text-slate-600">
                  <?php
                    $rows = $classTeachers[$class['id']] ?? [];
                    if (!$rows) {
                      echo '—';
                    } else {
                      $labels = [];
                      foreach ($rows as $row) {
                        $role = ($row['assignment_role'] ?? '') === 'MAIN' ? 'Main' : 'Asst';
                        $labels[] = htmlspecialchars($row['full_name']) . ' (' . $role . ')';
                      }
                      echo implode(', ', $labels);
                    }
                  ?>
                </td>
                <td class="px-4 py-3 text-slate-600"><?= htmlspecialchars($class['academic_year_label'] ?? '—') ?></td>
                <td class="px-4 py-3">
                  <?php if (!empty($class['is_locked_display'])): ?>
                    <span class="badge badge-danger">Locked</span>
                  <?php else: ?>
                    <span class="badge badge-success">Open</span>
                  <?php endif; ?>
                </td>
                <td class="px-4 py-3">
                  <a href="/attendance/<?= (int)$class['id'] ?>?date=<?= htmlspecialchars($date) ?>" class="btn btn-secondary btn-xs">Mark</a>
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
