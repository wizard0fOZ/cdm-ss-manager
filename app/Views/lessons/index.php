<?php
  $pageTitle = 'Lesson Plans';
  $pageSubtitle = 'Draft and publish weekly lessons.';

  ob_start();
?>
  <div class="flex flex-col gap-4">
    <div class="flex flex-wrap items-end justify-between gap-3">
      <form method="get" class="grid gap-3 md:grid-cols-5">
        <div>
          <label class="text-xs text-slate-500">Class</label>
          <select name="class_id" class="mt-1 w-full rounded-xl border border-slate-200 px-3 py-2 text-sm" data-enhance="search">
            <option value="">All</option>
            <?php foreach ($classes as $class): ?>
              <option value="<?= (int)$class['id'] ?>" <?= (string)$classId === (string)$class['id'] ? 'selected' : '' ?>><?= htmlspecialchars($class['name']) ?></option>
            <?php endforeach; ?>
          </select>
        </div>
        <div>
          <label class="text-xs text-slate-500">Status</label>
          <select name="status" class="mt-1 w-full rounded-xl border border-slate-200 px-3 py-2 text-sm" data-enhance="search">
            <option value="">All</option>
            <?php foreach ($statuses as $opt): ?>
              <option value="<?= $opt ?>" <?= $status === $opt ? 'selected' : '' ?>><?= $opt ?></option>
            <?php endforeach; ?>
          </select>
        </div>
        <div>
          <label class="text-xs text-slate-500">From</label>
          <input type="date" name="from" value="<?= htmlspecialchars($from ?? '') ?>" class="mt-1 w-full rounded-xl border border-slate-200 px-3 py-2 text-sm">
        </div>
        <div>
          <label class="text-xs text-slate-500">To</label>
          <input type="date" name="to" value="<?= htmlspecialchars($to ?? '') ?>" class="mt-1 w-full rounded-xl border border-slate-200 px-3 py-2 text-sm">
        </div>
        <div>
          <label class="text-xs text-slate-500">Search</label>
          <input name="q" value="<?= htmlspecialchars($q ?? '') ?>" placeholder="Title or content" class="mt-1 w-full rounded-xl border border-slate-200 px-3 py-2 text-sm">
        </div>
        <div class="flex items-end gap-2">
          <button class="rounded-xl bg-slate-900 px-4 py-2 text-sm font-semibold text-white">Filter</button>
          <a href="/lessons" class="rounded-xl border border-slate-200 px-4 py-2 text-sm">Reset</a>
        </div>
      </form>

      <a href="/lessons/create" class="inline-flex items-center justify-center rounded-xl bg-slate-900 px-4 py-2 text-sm font-semibold text-white">
        Add Lesson
      </a>
    </div>

    <div class="overflow-hidden rounded-2xl border border-slate-200">
      <table class="w-full text-left text-sm">
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
                <?php $message = 'No lesson plans found.'; ?>
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
                      echo '<div class="mt-1 text-xs text-slate-500">' . implode(', ', $labels) . '</div>';
                    }
                  ?>
                </td>
                <td class="px-4 py-3 font-semibold text-slate-900"><?= htmlspecialchars($lesson['title']) ?></td>
                <td class="px-4 py-3">
                  <span class="rounded-full bg-slate-100 px-3 py-1 text-xs text-slate-600"><?= htmlspecialchars($lesson['status']) ?></span>
                </td>
                <td class="px-4 py-3">
                  <div class="flex items-center gap-2 text-xs">
                    <a href="/lessons/<?= (int)$lesson['id'] ?>" class="rounded-lg border border-slate-200 px-3 py-1">View</a>
                    <a href="/lessons/<?= (int)$lesson['id'] ?>/edit" class="rounded-lg border border-slate-200 px-3 py-1">Edit</a>
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
