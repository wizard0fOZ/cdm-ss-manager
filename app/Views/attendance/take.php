<?php
  $cleanLabel = function ($value) {
    $value = trim((string)$value);
    $value = preg_replace('/_+/', ' ', $value);
    return trim($value, " _");
  };
  $className = $cleanLabel($class['name'] ?? 'Class');
  $pageTitle = 'Attendance - ' . $className;
  $pageSubtitle = 'Date: ' . htmlspecialchars($date);
  $csrf = $_SESSION['_csrf'] ?? '';

  $statuses = ['' => 'Unmarked', 'PRESENT' => 'Present', 'ABSENT' => 'Absent', 'LATE' => 'Late', 'EXCUSED' => 'Excused'];
  $reasons = ['' => 'Reason', 'SICK' => 'Sick', 'FAMILY' => 'Family', 'TRAVEL' => 'Travel', 'OTHER' => 'Other'];
  $summary = $summary ?? ['PRESENT' => 0, 'ABSENT' => 0, 'LATE' => 0, 'EXCUSED' => 0];

  ob_start();
?>
  <div class="flex flex-col gap-4">
    <div class="flex flex-wrap items-center justify-between gap-3">
      <div>
        <div class="text-xs uppercase tracking-wide text-slate-500">Class</div>
        <div class="text-lg font-semibold text-slate-900"><?= htmlspecialchars($className) ?></div>
        <div class="text-xs text-slate-500">Session: <?= htmlspecialchars($class['session_name'] ?? '—') ?></div>
        <?php if (!empty($teachers)): ?>
          <div class="mt-2 text-xs text-slate-500">
            <?php
              $labels = [];
              foreach ($teachers as $row) {
                $role = ($row['assignment_role'] ?? '') === 'MAIN' ? 'Main' : 'Asst';
                $labels[] = htmlspecialchars($row['full_name']) . ' (' . $role . ')';
              }
              echo implode(', ', $labels);
            ?>
          </div>
        <?php endif; ?>
      </div>
      <div class="flex items-center gap-2">
        <a href="/attendance?date=<?= htmlspecialchars($date) ?>" class="rounded-lg border border-slate-200 px-3 py-1 text-xs">Back</a>
        <?php if ($isAdmin && !$isLocked): ?>
          <form method="post" action="/attendance/<?= (int)$class['id'] ?>/lock?date=<?= htmlspecialchars($date) ?>">
            <input type="hidden" name="_csrf" value="<?= htmlspecialchars($csrf) ?>">
            <button class="rounded-lg border border-slate-200 px-3 py-1 text-xs">Lock Now</button>
          </form>
        <?php elseif ($isAdmin && $isLocked): ?>
          <form method="post" action="/attendance/<?= (int)$class['id'] ?>/unlock?date=<?= htmlspecialchars($date) ?>">
            <input type="hidden" name="_csrf" value="<?= htmlspecialchars($csrf) ?>">
            <button class="rounded-lg border border-slate-200 px-3 py-1 text-xs">Unlock</button>
          </form>
        <?php endif; ?>
      </div>
    </div>

    <?php if (!empty($errors)): ?>
      <div class="rounded-xl border border-rose-200 bg-rose-50 px-4 py-3 text-sm text-rose-700">
        <?php foreach ($errors as $error): ?>
          <div><?= htmlspecialchars($error) ?></div>
        <?php endforeach; ?>
      </div>
    <?php endif; ?>

    <?php if ($isLocked && !$isAdmin): ?>
      <div class="rounded-xl border border-rose-200 bg-rose-50 px-4 py-3 text-sm text-rose-700">
        Attendance is locked for this date. Contact coordinator to edit.
      </div>
    <?php endif; ?>

    <form method="post" action="/attendance/<?= (int)$class['id'] ?>?date=<?= htmlspecialchars($date) ?>" data-attendance-form>
      <input type="hidden" name="_csrf" value="<?= htmlspecialchars($csrf) ?>">

      <div class="flex flex-wrap items-center gap-2">
        <button type="button" class="rounded-lg border border-slate-200 px-3 py-1 text-xs" data-mark-all="PRESENT">Mark All Present</button>
        <button type="button" class="rounded-lg border border-slate-200 px-3 py-1 text-xs" data-mark-all="ABSENT">Mark All Absent</button>
        <button type="button" class="rounded-lg border border-slate-200 px-3 py-1 text-xs" data-mark-all="">Clear All</button>
        <span class="text-xs text-slate-400">Late can include a reason/note.</span>
      </div>

      <div class="mt-3 flex flex-wrap items-center gap-2">
        <span class="rounded-full bg-emerald-50 px-3 py-1 text-xs text-emerald-700">Present: <span data-count="PRESENT"><?= (int)$summary['PRESENT'] ?></span></span>
        <span class="rounded-full bg-rose-50 px-3 py-1 text-xs text-rose-700">Absent: <span data-count="ABSENT"><?= (int)$summary['ABSENT'] ?></span></span>
        <span class="rounded-full bg-amber-50 px-3 py-1 text-xs text-amber-700">Late: <span data-count="LATE"><?= (int)$summary['LATE'] ?></span></span>
        <span class="rounded-full bg-sky-50 px-3 py-1 text-xs text-sky-700">Excused: <span data-count="EXCUSED"><?= (int)$summary['EXCUSED'] ?></span></span>
        <span class="rounded-full bg-slate-100 px-3 py-1 text-xs text-slate-600">Unmarked: <span data-count="UNMARKED">0</span></span>
      </div>

      <div class="mt-4 overflow-hidden rounded-2xl border border-slate-200">
        <table class="w-full text-left text-sm">
          <thead class="bg-slate-50 text-xs uppercase tracking-wide text-slate-500">
            <tr>
              <th class="px-4 py-3">Student</th>
              <th class="px-4 py-3">Status</th>
              <th class="px-4 py-3">Tag</th>
              <th class="px-4 py-3">Note</th>
            </tr>
          </thead>
          <tbody>
            <?php if (empty($students)): ?>
              <tr>
                <td colspan="4" class="px-4 py-6">
                  <?php $message = 'No students enrolled in this class.'; ?>
                  <?php require __DIR__ . '/../partials/empty.php'; ?>
                </td>
              </tr>
            <?php else: ?>
              <?php foreach ($students as $student):
                $record = $records[$student['id']] ?? [];
              ?>
                <tr class="border-t border-slate-200">
                  <td class="px-4 py-3 font-semibold text-slate-900"><?= htmlspecialchars($student['full_name']) ?></td>
                  <td class="px-4 py-3">
                    <select name="status[<?= (int)$student['id'] ?>]" class="rounded-lg border border-slate-200 px-3 py-2 text-sm" <?= $isLocked && !$isAdmin ? 'disabled' : '' ?> data-status>
                      <?php foreach ($statuses as $value => $label): ?>
                        <option value="<?= $value ?>" <?= ($record['status'] ?? '') === $value ? 'selected' : '' ?>><?= $label ?></option>
                      <?php endforeach; ?>
                    </select>
                  </td>
                  <td class="px-4 py-3">
                    <select name="reason[<?= (int)$student['id'] ?>]" class="rounded-lg border border-slate-200 px-3 py-2 text-sm" <?= $isLocked && !$isAdmin ? 'disabled' : '' ?> data-reason>
                      <?php foreach ($reasons as $value => $label): ?>
                        <option value="<?= $value ?>" <?= ($record['absence_reason'] ?? '') === $value ? 'selected' : '' ?>><?= $label ?></option>
                      <?php endforeach; ?>
                    </select>
                  </td>
                  <td class="px-4 py-3">
                    <input name="note[<?= (int)$student['id'] ?>]" value="<?= htmlspecialchars($record['note'] ?? $record['absence_note'] ?? '') ?>" class="w-full rounded-lg border border-slate-200 px-3 py-2 text-sm" placeholder="Optional" <?= $isLocked && !$isAdmin ? 'disabled' : '' ?>>
                  </td>
                </tr>
              <?php endforeach; ?>
            <?php endif; ?>
          </tbody>
        </table>
      </div>

      <div class="mt-4 flex items-center gap-3">
        <button class="rounded-xl bg-slate-900 px-4 py-2 text-sm font-semibold text-white" type="submit" <?= $isLocked && !$isAdmin ? 'disabled' : '' ?>>
          Save Attendance
        </button>
        <a href="/attendance?date=<?= htmlspecialchars($date) ?>" class="text-sm text-slate-600">Cancel</a>
      </div>
    </form>
  </div>

  <script>
    (function () {
      const buttons = document.querySelectorAll('[data-mark-all]');
      const statusSelects = document.querySelectorAll('select[data-status]');
      const form = document.querySelector('[data-attendance-form]');

      function toggleReason() {
        statusSelects.forEach((sel) => {
          const row = sel.closest('tr');
          const reason = row?.querySelector('select[data-reason]');
          const needsReason = ['ABSENT','LATE','EXCUSED'].includes(sel.value);
          if (reason) {
            reason.disabled = !needsReason || sel.disabled;
            if (!needsReason) reason.value = '';
          }
        });
      }

      function updateCounts() {
        const counts = { PRESENT: 0, ABSENT: 0, LATE: 0, EXCUSED: 0, UNMARKED: 0 };
        statusSelects.forEach((sel) => {
          const v = sel.value || '';
          if (v === '') counts.UNMARKED += 1;
          else if (counts[v] !== undefined) counts[v] += 1;
        });
        Object.keys(counts).forEach((key) => {
          const el = document.querySelector(`[data-count=\"${key}\"]`);
          if (el) el.textContent = counts[key];
        });
      }

      buttons.forEach((btn) => {
        btn.addEventListener('click', () => {
          const value = btn.getAttribute('data-mark-all');
          statusSelects.forEach((sel) => { sel.value = value; });
          toggleReason();
          updateCounts();
        });
      });

      statusSelects.forEach((sel) => {
        sel.addEventListener('change', () => {
          toggleReason();
          updateCounts();
        });
      });

      if (form) {
        form.addEventListener('submit', (event) => {
          const unmarked = Array.from(statusSelects).filter((sel) => sel.value === '').length;
          if (unmarked > 0) {
            const ok = confirm(`There are ${unmarked} unmarked students. Continue?`);
            if (!ok) event.preventDefault();
          }
        });
      }

      toggleReason();
      updateCounts();
    })();
  </script>
<?php
  $content = ob_get_clean();
  require __DIR__ . '/../layout.php';
?>
