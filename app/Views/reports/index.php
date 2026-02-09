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
    <form method="get" class="filter-bar grid gap-3 md:grid-cols-4">
      <div>
        <label class="section-label">Report Type</label>
        <select name="type" class="mt-1.5 w-full rounded-xl border border-slate-200 px-3 py-2 text-sm" data-enhance="search">
          <?php foreach ($types as $key => $label): ?>
            <option value="<?= $key ?>" <?= $type === $key ? 'selected' : '' ?>><?= htmlspecialchars($label) ?></option>
          <?php endforeach; ?>
        </select>
      </div>
      <div>
        <label class="section-label">Academic Year</label>
        <select name="year_id" class="mt-1.5 w-full rounded-xl border border-slate-200 px-3 py-2 text-sm" data-enhance="search">
          <option value="">All</option>
          <?php foreach ($years as $year): ?>
            <option value="<?= (int)$year['id'] ?>" <?= (int)$yearId === (int)$year['id'] ? 'selected' : '' ?>><?= htmlspecialchars($year['label']) ?></option>
          <?php endforeach; ?>
        </select>
      </div>
      <div>
        <label class="section-label">Term</label>
        <select name="term_id" class="mt-1.5 w-full rounded-xl border border-slate-200 px-3 py-2 text-sm" data-enhance="search">
          <option value="">All</option>
          <?php foreach ($terms as $term): ?>
            <option value="<?= (int)$term['id'] ?>" <?= (int)$termId === (int)$term['id'] ? 'selected' : '' ?>><?= htmlspecialchars($term['label']) ?></option>
          <?php endforeach; ?>
        </select>
      </div>
      <div>
        <label class="section-label">Class</label>
        <select name="class_id" class="mt-1.5 w-full rounded-xl border border-slate-200 px-3 py-2 text-sm" data-enhance="search">
          <option value="">All</option>
          <?php foreach ($classes as $class): ?>
            <option value="<?= (int)$class['id'] ?>" <?= (int)$classId === (int)$class['id'] ? 'selected' : '' ?>><?= htmlspecialchars($class['name']) ?></option>
          <?php endforeach; ?>
        </select>
      </div>
      <div>
        <label class="section-label">Student</label>
        <select name="student_id" class="mt-1.5 w-full rounded-xl border border-slate-200 px-3 py-2 text-sm" data-enhance="search">
          <option value="">All</option>
          <?php foreach ($students as $student): ?>
            <option value="<?= (int)$student['id'] ?>" <?= (int)$studentId === (int)$student['id'] ? 'selected' : '' ?>><?= htmlspecialchars($student['full_name']) ?></option>
          <?php endforeach; ?>
        </select>
      </div>
      <div>
        <label class="section-label">Attendance Status</label>
        <select name="status" class="mt-1.5 w-full rounded-xl border border-slate-200 px-3 py-2 text-sm">
          <option value="">All</option>
          <?php foreach (['PRESENT','ABSENT','LATE','EXCUSED','UNMARKED'] as $s): ?>
            <option value="<?= $s ?>" <?= $status === $s ? 'selected' : '' ?>><?= $s ?></option>
          <?php endforeach; ?>
        </select>
      </div>
      <div>
        <label class="section-label">From</label>
        <input type="date" name="from" value="<?= htmlspecialchars($from) ?>" class="mt-1.5 w-full rounded-xl border border-slate-200 px-3 py-2 text-sm">
      </div>
      <div>
        <label class="section-label">To</label>
        <input type="date" name="to" value="<?= htmlspecialchars($to) ?>" class="mt-1.5 w-full rounded-xl border border-slate-200 px-3 py-2 text-sm">
      </div>
      <div class="flex items-end gap-2 md:col-span-4">
        <button class="btn btn-primary btn-sm">
          <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polygon points="22 3 2 3 10 12.46 10 19 14 21 14 12.46 22 3"/></svg>
          Run
        </button>
        <a href="/reports" class="btn btn-secondary btn-sm">Reset</a>
        <a href="/reports/pdf?<?= htmlspecialchars($query) ?>" class="btn btn-secondary btn-sm">
          <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><polyline points="14 2 14 8 20 8"/></svg>
          PDF
        </a>
        <a href="/reports/csv?<?= htmlspecialchars($query) ?>" class="btn btn-secondary btn-sm">
          <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/><polyline points="7 10 12 15 17 10"/><line x1="12" y1="15" x2="12" y2="3"/></svg>
          CSV
        </a>
      </div>
    </form>

    <div class="rounded-2xl border border-slate-200 bg-white p-4">
      <div class="text-sm font-semibold text-slate-900"><?= htmlspecialchars($report['title'] ?? 'Report') ?></div>
      <?php if (!empty($report['subtitle'])): ?>
        <div class="text-xs text-slate-500 mt-1"><?= htmlspecialchars($report['subtitle']) ?></div>
      <?php endif; ?>
      <?php if (!empty($report['note'])): ?>
        <div class="mt-2 text-sm text-amber-700"><?= htmlspecialchars($report['note']) ?></div>
      <?php endif; ?>
      <div class="mt-2 text-xs text-slate-500">Rows: <?= count($report['rows'] ?? []) ?></div>

      <div class="mt-3 table-wrap overflow-x-auto">
        <table class="cdm-table w-full text-left text-sm">
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
