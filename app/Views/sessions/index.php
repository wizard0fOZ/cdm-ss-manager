<?php
  $pageTitle = 'Sessions';
  $pageSubtitle = 'Define Sunday time blocks for classes.';

  ob_start();
?>
  <div class="flex items-center justify-between">
    <div class="section-label">Sessions represent Sunday time blocks.</div>
    <a href="/sessions/create" class="btn btn-primary btn-sm">
      <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/></svg>
      Add Session
    </a>
  </div>

  <div class="mt-4 table-wrap overflow-x-auto rounded-2xl border border-slate-200">
    <table class="cdm-table w-full text-left text-sm">
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
              <?php
                $message = 'No sessions found. Add the Sunday time blocks first.';
                $actionLabel = 'Add Session';
                $actionHref = '/sessions/create';
              ?>
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
                <a href="/sessions/<?= (int)$session['id'] ?>/edit" class="btn btn-secondary btn-xs">Edit</a>
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
