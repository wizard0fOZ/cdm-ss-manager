<?php
  $pageTitle = 'Terms';
  $pageSubtitle = 'Two terms per academic year with manual dates.';

  ob_start();
?>
  <div class="flex items-center justify-between">
    <div class="section-label">Create two terms for each academic year.</div>
    <a href="/terms/create" class="btn btn-primary btn-sm">
      <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/></svg>
      Add Term
    </a>
  </div>

  <div class="mt-4 table-wrap overflow-x-auto rounded-2xl border border-slate-200">
    <table class="cdm-table w-full text-left text-sm">
      <thead class="bg-slate-50 text-xs uppercase tracking-wide text-slate-500">
        <tr>
          <th class="px-4 py-3">Academic Year</th>
          <th class="px-4 py-3">Term</th>
          <th class="px-4 py-3">Label</th>
          <th class="px-4 py-3">Start</th>
          <th class="px-4 py-3">End</th>
          <th class="px-4 py-3">Actions</th>
        </tr>
      </thead>
      <tbody>
        <?php if (empty($terms)): ?>
        <tr>
          <td colspan="6" class="px-4 py-6">
              <?php
                $message = 'No terms found. Add two terms for each academic year.';
                $actionLabel = 'Add Term';
                $actionHref = '/terms/create';
              ?>
              <?php require __DIR__ . '/../partials/empty.php'; ?>
          </td>
        </tr>
        <?php else: ?>
          <?php foreach ($terms as $term): ?>
            <tr class="border-t border-slate-200">
              <td class="px-4 py-3 font-semibold text-slate-900"><?= htmlspecialchars($term['academic_year_label']) ?></td>
              <td class="px-4 py-3 text-slate-600">Term <?= (int)$term['term_number'] ?></td>
              <td class="px-4 py-3 text-slate-600"><?= htmlspecialchars($term['label']) ?></td>
              <td class="px-4 py-3 text-slate-600"><?= htmlspecialchars($term['start_date']) ?></td>
              <td class="px-4 py-3 text-slate-600"><?= htmlspecialchars($term['end_date']) ?></td>
              <td class="px-4 py-3">
                <a href="/terms/<?= (int)$term['id'] ?>/edit" class="btn btn-secondary btn-xs">Edit</a>
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
