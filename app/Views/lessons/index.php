<?php
  $pageTitle = 'Lesson Plans';
  $pageSubtitle = 'Draft and publish weekly lessons.';

  $lessonBadge = [
    'DRAFT' => 'badge-neutral',
    'PUBLISHED' => 'badge-success',
    'APPROVED' => 'badge-info',
    'ARCHIVED' => 'badge-warning',
  ];

  ob_start();
?>
  <div class="flex flex-col gap-4">
    <div class="flex flex-col gap-3 lg:flex-row lg:items-end lg:justify-between">
      <form method="get" class="filter-bar grid flex-1 gap-3 md:grid-cols-5">
        <div>
          <label class="section-label">Class</label>
          <select name="class_id" class="mt-1.5 w-full rounded-xl border border-slate-200 px-3 py-2 text-sm" data-enhance="search">
            <option value="">All</option>
            <?php foreach ($classes as $class): ?>
              <option value="<?= (int)$class['id'] ?>" <?= (string)$classId === (string)$class['id'] ? 'selected' : '' ?>><?= htmlspecialchars($class['name']) ?></option>
            <?php endforeach; ?>
          </select>
        </div>
        <div>
          <label class="section-label">Status</label>
          <select name="status" class="mt-1.5 w-full rounded-xl border border-slate-200 px-3 py-2 text-sm" data-enhance="search">
            <option value="">All</option>
            <?php foreach ($statuses as $opt): ?>
              <option value="<?= $opt ?>" <?= $status === $opt ? 'selected' : '' ?>><?= $opt ?></option>
            <?php endforeach; ?>
          </select>
        </div>
        <div>
          <label class="section-label">From</label>
          <input type="date" name="from" value="<?= htmlspecialchars($from ?? '') ?>" class="mt-1.5 w-full rounded-xl border border-slate-200 px-3 py-2 text-sm">
        </div>
        <div>
          <label class="section-label">To</label>
          <input type="date" name="to" value="<?= htmlspecialchars($to ?? '') ?>" class="mt-1.5 w-full rounded-xl border border-slate-200 px-3 py-2 text-sm">
        </div>
        <div>
          <label class="section-label">Search</label>
          <input name="q" value="<?= htmlspecialchars($q ?? '') ?>" placeholder="Title or content" class="mt-1.5 w-full rounded-xl border border-slate-200 px-3 py-2 text-sm">
        </div>
        <div class="flex items-end gap-2">
          <button class="btn btn-primary btn-sm">Filter</button>
          <a href="/lessons" class="btn btn-secondary btn-sm">Reset</a>
        </div>
      </form>

      <a href="/lessons/create" class="btn btn-primary btn-sm">
        <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/></svg>
        Add Lesson
      </a>
    </div>

    <div class="table-wrap overflow-x-auto rounded-2xl border border-slate-200">
      <table class="cdm-table w-full text-left text-sm">
        <thead class="bg-slate-50 text-xs uppercase tracking-wide text-slate-500">
          <tr>
            <th class="px-4 py-3">Date</th>
            <th class="px-4 py-3">Class</th>
            <th class="px-4 py-3">Title</th>
            <th class="px-4 py-3">Status</th>
            <th class="px-4 py-3">Actions</th>
          </tr>
        </thead>
        <tbody>
          <?php if (empty($lessons)): ?>
            <tr>
              <td colspan="5" class="px-4 py-6">
                <?php
                  $message = 'No lesson plans found.';
                  $actionLabel = 'Add Lesson';
                  $actionHref = '/lessons/create';
                ?>
                <?php require __DIR__ . '/../partials/empty.php'; ?>
              </td>
            </tr>
          <?php else: ?>
            <?php foreach ($lessons as $lesson): ?>
              <tr class="border-t border-slate-200">
                <td class="px-4 py-3 text-slate-600"><?= htmlspecialchars($lesson['session_date']) ?></td>
                <td class="px-4 py-3 text-slate-600">
                  <div class="font-semibold text-slate-900"><?= htmlspecialchars($lesson['class_name']) ?></div>
                  <?php
                    $teacherRows = $classTeachers[(int)$lesson['class_id']] ?? [];
                    if ($teacherRows) {
                      $labels = [];
                      foreach ($teacherRows as $row) {
                        $role = ($row['assignment_role'] ?? '') === 'MAIN' ? 'Main' : 'Asst';
                        $labels[] = htmlspecialchars($row['full_name']) . ' (' . $role . ')';
                      }
                      echo '<div class="mt-1 text-xs text-slate-400">' . implode(', ', $labels) . '</div>';
                    }
                  ?>
                </td>
                <td class="px-4 py-3 font-semibold text-slate-900"><?= htmlspecialchars($lesson['title']) ?></td>
                <td class="px-4 py-3">
                  <span class="badge <?= $lessonBadge[$lesson['status']] ?? 'badge-neutral' ?>"><?= htmlspecialchars($lesson['status']) ?></span>
                </td>
                <td class="px-4 py-3">
                  <div class="flex items-center gap-1">
                    <a href="/lessons/<?= (int)$lesson['id'] ?>" class="btn btn-secondary btn-xs">View</a>
                    <a href="/lessons/<?= (int)$lesson['id'] ?>/edit" class="btn btn-ghost btn-xs">Edit</a>
                  </div>
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
