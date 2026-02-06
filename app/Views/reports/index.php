<?php
  $pageTitle = 'Reports';
  $pageSubtitle = 'Generate PDF reports for attendance, faith book, and training.';

  $types = $types ?? [];
  $type = $type ?? 'attendance_class';
  $yearId = $yearId ?? 0;
  $termId = $termId ?? 0;
  $classId = $classId ?? 0;
  $studentId = $studentId ?? 0;
  $status = $status ?? '';
  $from = $from ?? '';
  $to = $to ?? '';
  $report = $report ?? ['rows' => []];

  $query = http_build_query(array_filter([
    'type' => $type,
    'year_id' => $yearId ?: null,
    'term_id' => $termId ?: null,
    'class_id' => $classId ?: null,
    'student_id' => $studentId ?: null,
    'status' => $status ?: null,
    'from' => $from ?: null,
    'to' => $to ?: null,
  ]));

  ob_start();
?>
  <div class="flex flex-col gap-4">
    <form method="get" class="grid gap-3 md:grid-cols-6">
      <div>
        <label class="text-xs text-slate-500">Report Type</label>
        <select name="type" class="mt-1 w-full rounded-xl border border-slate-200 px-3 py-2 text-sm" data-enhance="search">
          <?php foreach ($types as $key => $label): ?>
            <option value="<?= $key ?>" <?= $type === $key ? 'selected' : '' ?>><?= htmlspecialchars($label) ?></option>
          <?php endforeach; ?>
        </select>
      </div>
      <div>
        <label class="text-xs text-slate-500">Academic Year</label>
        <select name="year_id" class="mt-1 w-full rounded-xl border border-slate-200 px-3 py-2 text-sm" data-enhance="search">
          <option value="">All</option>
          <?php foreach ($years as $year): ?>
            <option value="<?= (int)$year['id'] ?>" <?= (int)$yearId === (int)$year['id'] ? 'selected' : '' ?>><?= htmlspecialchars($year['label']) ?></option>
          <?php endforeach; ?>
        </select>
      </div>
      <div>
        <label class="text-xs text-slate-500">Term</label>
        <select name="term_id" class="mt-1 w-full rounded-xl border border-slate-200 px-3 py-2 text-sm" data-enhance="search">
          <option value="">All</option>
          <?php foreach ($terms as $term): ?>
            <option value="<?= (int)$term['id'] ?>" <?= (int)$termId === (int)$term['id'] ? 'selected' : '' ?>><?= htmlspecialchars($term['label']) ?></option>
          <?php endforeach; ?>
        </select>
      </div>
      <div>
        <label class="text-xs text-slate-500">Class</label>
        <select name="class_id" class="mt-1 w-full rounded-xl border border-slate-200 px-3 py-2 text-sm" data-enhance="search">
          <option value="">All</option>
          <?php foreach ($classes as $class): ?>
            <option value="<?= (int)$class['id'] ?>" <?= (int)$classId === (int)$class['id'] ? 'selected' : '' ?>><?= htmlspecialchars($class['name']) ?></option>
          <?php endforeach; ?>
        </select>
      </div>
      <div>
        <label class="text-xs text-slate-500">Student</label>
        <select name="student_id" class="mt-1 w-full rounded-xl border border-slate-200 px-3 py-2 text-sm" data-enhance="search">
          <option value="">All</option>
          <?php foreach ($students as $student): ?>
            <option value="<?= (int)$student['id'] ?>" <?= (int)$studentId === (int)$student['id'] ? 'selected' : '' ?>><?= htmlspecialchars($student['full_name']) ?></option>
          <?php endforeach; ?>
        </select>
      </div>
      <div>
        <label class="text-xs text-slate-500">Attendance Status</label>
        <select name="status" class="mt-1 w-full rounded-xl border border-slate-200 px-3 py-2 text-sm">
          <option value="">All</option>
          <?php foreach (['PRESENT','ABSENT','LATE','EXCUSED','UNMARKED'] as $s): ?>
            <option value="<?= $s ?>" <?= $status === $s ? 'selected' : '' ?>><?= $s ?></option>
          <?php endforeach; ?>
        </select>
      </div>
      <div>
        <label class="text-xs text-slate-500">From</label>
        <input type="date" name="from" value="<?= htmlspecialchars($from) ?>" class="mt-1 w-full rounded-xl border border-slate-200 px-3 py-2 text-sm">
      </div>
      <div>
        <label class="text-xs text-slate-500">To</label>
        <input type="date" name="to" value="<?= htmlspecialchars($to) ?>" class="mt-1 w-full rounded-xl border border-slate-200 px-3 py-2 text-sm">
      </div>
      <div class="flex items-end gap-2 md:col-span-6">
        <button class="rounded-xl bg-slate-900 px-4 py-2 text-sm font-semibold text-white">Run</button>
        <a href="/reports" class="rounded-xl border border-slate-200 px-4 py-2 text-sm">Reset</a>
        <a href="/reports/pdf?<?= htmlspecialchars($query) ?>" class="rounded-xl border border-slate-200 px-4 py-2 text-sm">Download PDF</a>
        <a href="/reports/csv?<?= htmlspecialchars($query) ?>" class="rounded-xl border border-slate-200 px-4 py-2 text-sm">Download CSV</a>
      </div>
    </form>

    <div class="rounded-xl border border-slate-200 bg-white p-4">
      <div class="text-sm font-semibold text-slate-900"><?= htmlspecialchars($report['title'] ?? 'Report') ?></div>
      <?php if (!empty($report['subtitle'])): ?>
        <div class="text-xs text-slate-500 mt-1"><?= htmlspecialchars($report['subtitle']) ?></div>
      <?php endif; ?>
      <?php if (!empty($report['note'])): ?>
        <div class="mt-2 text-sm text-amber-700"><?= htmlspecialchars($report['note']) ?></div>
      <?php endif; ?>
      <div class="mt-2 text-xs text-slate-500">Rows: <?= count($report['rows'] ?? []) ?></div>

      <div class="mt-3 overflow-x-auto">
        <table class="w-full text-left text-sm">
          <thead class="bg-slate-50 text-xs uppercase tracking-wide text-slate-500">
            <tr>
              <?php foreach ($report['headers'] ?? [] as $h): ?>
                <th class="px-3 py-2"><?= htmlspecialchars($h) ?></th>
              <?php endforeach; ?>
            </tr>
          </thead>
          <tbody>
            <?php if (empty($report['rows'])): ?>
              <tr>
                <td colspan="<?= count($report['headers'] ?? []) ?: 1 ?>" class="px-3 py-4 text-slate-500">No data.</td>
              </tr>
            <?php else: ?>
              <?php foreach ($report['rows'] as $row): ?>
                <tr class="border-t border-slate-200">
                  <?php foreach ($report['headers'] as $header): ?>
                    <?php
                      $cell = '';
                      if ($type === 'attendance_class') {
                        $map = ['Student' => 'student', 'Present' => 'present', 'Absent' => 'absent', 'Late' => 'late', 'Excused' => 'excused', 'Unmarked' => 'unmarked'];
                        $cell = $row[$map[$header] ?? ''] ?? '';
                      } elseif ($type === 'attendance_student') {
                        $map = ['Present' => 'present', 'Absent' => 'absent', 'Late' => 'late', 'Excused' => 'excused', 'Unmarked' => 'unmarked'];
                        $cell = $row[$map[$header] ?? ''] ?? '';
                      } elseif ($type === 'faithbook') {
                        $map = ['Date' => 'entry_date', 'Type' => 'entry_type', 'Title' => 'title', 'Content' => 'content'];
                        $cell = $row[$map[$header] ?? ''] ?? '';
                      } elseif ($type === 'training') {
                        $map = ['Teacher' => 'full_name', 'PSO' => 'pso_date', 'Formation' => 'formation_date'];
                        $cell = $row[$map[$header] ?? ''] ?? '';
                      } elseif ($type === 'class_list') {
                        $map = ['Student' => 'full_name', 'DOB' => 'dob', 'Status' => 'status'];
                        $cell = $row[$map[$header] ?? ''] ?? '';
                      }
                    ?>
                    <td class="px-3 py-2 text-slate-600"><?= htmlspecialchars((string)$cell) ?></td>
                  <?php endforeach; ?>
                </tr>
              <?php endforeach; ?>
            <?php endif; ?>
          </tbody>
        </table>
      </div>
    </div>
  </div>
<?php
  $content = ob_get_clean();
  require __DIR__ . '/../layout.php';
?>
