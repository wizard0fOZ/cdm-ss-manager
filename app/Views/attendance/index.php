<?php
  $pageTitle = 'Attendance';
  $pageSubtitle = 'Select a class to mark attendance.';

  ob_start();
?>
  <div class="flex flex-col gap-4">
    <form method="get" class="flex flex-wrap items-end gap-3 rounded-2xl border border-slate-200 bg-slate-50 p-3">
      <div>
        <label class="text-xs text-slate-500">Date</label>
        <input type="date" name="date" value="<?= htmlspecialchars($date) ?>" class="mt-1 rounded-lg border border-slate-200 px-3 py-2 text-sm">
      </div>
      <div>
        <label class="text-xs text-slate-500">Session</label>
        <select name="session_id" class="mt-1 rounded-lg border border-slate-200 px-3 py-2 text-sm" data-enhance="search">
          <option value="">All</option>
          <?php foreach ($sessions as $session): ?>
            <option value="<?= (int)$session['id'] ?>" <?= (string)$sessionId === (string)$session['id'] ? 'selected' : '' ?>><?= htmlspecialchars($session['name']) ?></option>
          <?php endforeach; ?>
        </select>
      </div>
      <div>
        <label class="text-xs text-slate-500">Program</label>
        <select name="program" class="mt-1 rounded-lg border border-slate-200 px-3 py-2 text-sm" data-enhance="search">
          <option value="">All</option>
          <?php foreach ($programs as $p): ?>
            <option value="<?= htmlspecialchars($p['program']) ?>" <?= ($program ?? '') === $p['program'] ? 'selected' : '' ?>><?= htmlspecialchars($p['program']) ?></option>
          <?php endforeach; ?>
        </select>
      </div>
      <div>
        <label class="text-xs text-slate-500">Class</label>
        <select name="class_id" class="mt-1 rounded-lg border border-slate-200 px-3 py-2 text-sm" data-enhance="search">
          <option value="">All</option>
          <?php foreach ($classes as $class): ?>
            <option value="<?= (int)$class['id'] ?>" <?= (string)$classId === (string)$class['id'] ? 'selected' : '' ?>><?= htmlspecialchars($class['name']) ?></option>
          <?php endforeach; ?>
        </select>
      </div>
      <button class="rounded-lg bg-slate-900 px-4 py-2 text-sm font-semibold text-white">Load</button>
      <a href="/attendance" class="rounded-lg border border-slate-200 px-4 py-2 text-sm">Reset</a>
      <span class="text-xs text-slate-400">Sunday locks at 11:59pm.</span>
    </form>

    <div class="overflow-hidden rounded-2xl border border-slate-200">
      <table class="w-full text-left text-sm">
        <thead class="bg-slate-50 text-xs uppercase tracking-wide text-slate-500">
          <tr>
            <th class="px-4 py-3">Class</th>
            <th class="px-4 py-3">Session</th>
            <th class="px-4 py-3">Year</th>
            <th class="px-4 py-3">Status</th>
            <th class="px-4 py-3">Actions</th>
          </tr>
        </thead>
        <tbody>
          <?php if (empty($classes)): ?>
            <tr>
              <td colspan="5" class="px-4 py-6">
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
                <td class="px-4 py-3 text-slate-600"><?= htmlspecialchars($class['academic_year_label'] ?? '—') ?></td>
                <td class="px-4 py-3">
                  <?php if (!empty($class['is_locked_display'])): ?>
                    <span class="rounded-full bg-rose-100 px-3 py-1 text-xs text-rose-700">Locked</span>
                  <?php else: ?>
                    <span class="rounded-full bg-emerald-100 px-3 py-1 text-xs text-emerald-700">Open</span>
                  <?php endif; ?>
                </td>
                <td class="px-4 py-3">
                  <a href="/attendance/<?= (int)$class['id'] ?>?date=<?= htmlspecialchars($date) ?>" class="rounded-lg border border-slate-200 px-3 py-1 text-xs">Mark</a>
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
