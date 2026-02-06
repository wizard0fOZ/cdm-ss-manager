<?php
  $pageTitle = 'Classes';
  $pageSubtitle = 'Manage class lists by program and session.';

  ob_start();
?>
  <div class="flex items-center justify-between">
    <div class="text-sm text-slate-600">Assign classes to academic years and sessions.</div>
    <a href="/classes/create" class="rounded-xl bg-slate-900 px-4 py-2 text-sm font-semibold text-white">Add Class</a>
  </div>

  <div class="mt-6 overflow-hidden rounded-2xl border border-slate-200">
    <table class="w-full text-left text-sm">
      <thead class="bg-slate-50 text-xs uppercase tracking-wide text-slate-500">
        <tr>
          <th class="px-4 py-3">Name</th>
          <th class="px-4 py-3">Program</th>
          <th class="px-4 py-3">Grade</th>
          <th class="px-4 py-3">Stream</th>
          <th class="px-4 py-3">Session</th>
          <th class="px-4 py-3">Academic Year</th>
          <th class="px-4 py-3">Status</th>
          <th class="px-4 py-3">Actions</th>
        </tr>
      </thead>
      <tbody>
        <?php if (empty($classes)): ?>
          <tr>
            <td colspan="8" class="px-4 py-6">
              <?php $message = 'No classes found. Add your first class.'; ?>
              <?php require __DIR__ . '/../partials/empty.php'; ?>
            </td>
          </tr>
        <?php else: ?>
          <?php foreach ($classes as $class): ?>
            <tr class="border-t border-slate-200">
              <td class="px-4 py-3 font-semibold text-slate-900"><?= htmlspecialchars($class['name']) ?></td>
              <td class="px-4 py-3 text-slate-600"><?= htmlspecialchars($class['program']) ?></td>
              <td class="px-4 py-3 text-slate-600"><?= htmlspecialchars($class['grade_level'] ?? '—') ?></td>
              <td class="px-4 py-3 text-slate-600"><?= htmlspecialchars($class['stream']) ?></td>
              <td class="px-4 py-3 text-slate-600"><?= htmlspecialchars($class['session_name'] ?? '—') ?></td>
              <td class="px-4 py-3 text-slate-600"><?= htmlspecialchars($class['academic_year_label'] ?? '—') ?></td>
              <td class="px-4 py-3">
                <span class="rounded-full bg-slate-100 px-3 py-1 text-xs text-slate-600"><?= htmlspecialchars($class['status']) ?></span>
              </td>
              <td class="px-4 py-3">
                <a href="/classes/<?= (int)$class['id'] ?>/edit" class="rounded-lg border border-slate-200 px-3 py-1 text-xs">Edit</a>
              </td>
            </tr>
          <?php endforeach; ?>
        <?php endif; ?>
      </tbody>
    </table>
  </div>
<?php
  $content = ob_get_clean();
  require __DIR__ . '/../layout.php';
?>
