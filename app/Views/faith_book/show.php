<?php
  $pageTitle = 'Faith Book - ' . ($student['full_name'] ?? 'Student');
  $pageSubtitle = 'Notes, entries, and attendance summary.';

  ob_start();
?>
  <div class="flex flex-col gap-6">
    <div class="flex flex-wrap items-center justify-between gap-3">
      <div>
        <div class="text-xs uppercase tracking-wide text-slate-500">Student</div>
        <div class="text-lg font-semibold text-slate-900"><?= htmlspecialchars($student['full_name'] ?? '') ?></div>
  <div class="text-xs text-slate-500">Class: <?= htmlspecialchars($student['class_name'] ?? '—') ?></div>
  <?php if (!empty($teachers)): ?>
    <div class="mt-1 text-xs text-slate-500">
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
        <a href="/faith-book/<?= (int)$student['id'] ?>/create" class="btn btn-primary">Add Entry</a>
        <a href="/faith-book/<?= (int)$student['id'] ?>/pdf" class="btn btn-secondary">Download PDF</a>
        <a href="/faith-book/<?= (int)$student['id'] ?>/print" class="btn btn-secondary" target="_blank" rel="noreferrer">Print</a>
        <a href="/faith-book/<?= (int)$student['id'] ?>/export" class="btn btn-secondary">Export CSV</a>
      </div>
    </div>

    <div class="rounded-2xl border border-slate-200 p-4">
      <div class="text-xs uppercase tracking-wide text-slate-500">Attendance by Term</div>
      <?php if (empty($attendance)): ?>
        <p class="mt-3 text-sm text-slate-600">No term records found.</p>
      <?php else: ?>
        <div class="mt-3 grid gap-3 md:grid-cols-2">
          <?php foreach ($attendance as $term): ?>
            <div class="rounded-xl border border-slate-200 bg-slate-50 p-4">
              <div class="text-sm font-semibold text-slate-900"><?= htmlspecialchars($term['term']) ?></div>
              <div class="text-xs text-slate-500"><?= htmlspecialchars($term['start_date']) ?> to <?= htmlspecialchars($term['end_date']) ?></div>
              <div class="mt-3 flex flex-wrap gap-2 text-xs">
                <span class="rounded-full bg-emerald-50 px-3 py-1 text-emerald-700">Present: <?= (int)$term['counts']['PRESENT'] ?></span>
                <span class="rounded-full bg-rose-50 px-3 py-1 text-rose-700">Absent: <?= (int)$term['counts']['ABSENT'] ?></span>
                <span class="rounded-full bg-amber-50 px-3 py-1 text-amber-700">Late: <?= (int)$term['counts']['LATE'] ?></span>
                <span class="rounded-full bg-sky-50 px-3 py-1 text-sky-700">Excused: <?= (int)$term['counts']['EXCUSED'] ?></span>
              </div>
            </div>
          <?php endforeach; ?>
        </div>
      <?php endif; ?>
    </div>

    <div class="rounded-2xl border border-slate-200 p-4">
      <div class="text-xs uppercase tracking-wide text-slate-500">Entries</div>
      <?php if (empty($entries)): ?>
        <p class="mt-3 text-sm text-slate-600">No entries yet.</p>
      <?php else: ?>
        <div class="mt-3 space-y-3">
          <?php foreach ($entries as $entry): ?>
            <div class="rounded-xl border border-slate-200 bg-white p-4">
              <div class="flex items-center justify-between">
                <div>
                  <div class="text-sm font-semibold text-slate-900"><?= htmlspecialchars($entry['title']) ?></div>
                  <div class="text-xs text-slate-500"><?= htmlspecialchars($entry['entry_date']) ?> • <?= htmlspecialchars($entry['entry_type']) ?></div>
                </div>
              </div>
              <div class="mt-2 text-sm text-slate-700 whitespace-pre-wrap"><?= htmlspecialchars($entry['content']) ?></div>
            </div>
          <?php endforeach; ?>
        </div>
      <?php endif; ?>
    </div>
  </div>
<?php
  $content = ob_get_clean();
  require __DIR__ . '/../layout.php';
?>
