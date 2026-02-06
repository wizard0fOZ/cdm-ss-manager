<?php
  $pageTitle = 'Sessions';
  $pageSubtitle = 'Define Sunday time blocks for classes.';

  ob_start();
?>
  <div class="flex items-center justify-between">
    <div class="text-sm text-slate-600">Sessions represent Sunday time blocks.</div>
    <a href="/sessions/create" class="rounded-xl bg-slate-900 px-4 py-2 text-sm font-semibold text-white">Add Session</a>
  </div>

  <div class="mt-6 overflow-hidden rounded-2xl border border-slate-200">
    <table class="w-full text-left text-sm">
      <thead class="bg-slate-50 text-xs uppercase tracking-wide text-slate-500">
        <tr>
          <th class="px-4 py-3">Name</th>
          <th class="px-4 py-3">Start</th>
          <th class="px-4 py-3">End</th>
          <th class="px-4 py-3">Sort</th>
          <th class="px-4 py-3">Actions</th>
        </tr>
      </thead>
      <tbody>
        <?php if (empty($sessions)): ?>
          <tr>
            <td colspan="5" class="px-4 py-6">
              <?php $message = 'No sessions found. Add the Sunday time blocks first.'; ?>
              <?php require __DIR__ . '/../partials/empty.php'; ?>
            </td>
          </tr>
        <?php else: ?>
          <?php foreach ($sessions as $session): ?>
            <tr class="border-t border-slate-200">
              <td class="px-4 py-3 font-semibold text-slate-900"><?= htmlspecialchars($session['name']) ?></td>
              <td class="px-4 py-3 text-slate-600"><?= htmlspecialchars($session['start_time']) ?></td>
              <td class="px-4 py-3 text-slate-600"><?= htmlspecialchars($session['end_time']) ?></td>
              <td class="px-4 py-3 text-slate-600"><?= (int)$session['sort_order'] ?></td>
              <td class="px-4 py-3">
                <a href="/sessions/<?= (int)$session['id'] ?>/edit" class="rounded-lg border border-slate-200 px-3 py-1 text-xs">Edit</a>
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
