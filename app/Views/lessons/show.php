<?php
  $pageTitle = 'Lesson Plan';
  $pageSubtitle = htmlspecialchars($lesson['class_name'] ?? '');

  ob_start();
?>
  <div class="flex flex-col gap-6">
    <div class="flex items-center justify-between">
      <div>
        <div class="text-xs uppercase tracking-wide text-slate-500">Lesson Date</div>
        <div class="text-lg font-semibold text-slate-900"><?= htmlspecialchars($lesson['session_date'] ?? '') ?></div>
      </div>
      <div class="flex items-center gap-2">
        <a href="/lessons/<?= (int)$lesson['id'] ?>/copy" class="rounded-xl border border-slate-200 px-4 py-2 text-sm">Copy</a>
        <a href="/lessons/<?= (int)$lesson['id'] ?>/print" class="rounded-xl border border-slate-200 px-4 py-2 text-sm" target="_blank" rel="noreferrer">Print</a>
        <a href="/lessons/<?= (int)$lesson['id'] ?>/edit" class="rounded-xl border border-slate-200 px-4 py-2 text-sm">Edit</a>
        <a href="/lessons" class="rounded-xl border border-slate-200 px-4 py-2 text-sm">Back</a>
      </div>
    </div>

    <div class="grid gap-4 md:grid-cols-2">
      <div class="rounded-xl border border-slate-200 p-4">
        <div class="text-xs uppercase tracking-wide text-slate-500">Class</div>
        <div class="mt-2 text-sm text-slate-900"><?= htmlspecialchars($lesson['class_name'] ?? '') ?></div>
        <?php if (!empty($classTeachers)): ?>
          <div class="mt-2 text-xs text-slate-500">
            <?php
              $labels = [];
              foreach ($classTeachers as $row) {
                $role = ($row['assignment_role'] ?? '') === 'MAIN' ? 'Main' : 'Asst';
                $labels[] = htmlspecialchars($row['full_name']) . ' (' . $role . ')';
              }
              echo implode(', ', $labels);
            ?>
          </div>
        <?php endif; ?>
        <div class="mt-2 text-xs text-slate-500">Session: <?= htmlspecialchars($lesson['session_name'] ?? '—') ?></div>
        <div class="mt-3">
          <span class="rounded-full bg-slate-100 px-3 py-1 text-xs text-slate-600"><?= htmlspecialchars($lesson['status'] ?? '') ?></span>
        </div>
      </div>
      <div class="rounded-xl border border-slate-200 p-4">
        <div class="text-xs uppercase tracking-wide text-slate-500">Title</div>
        <div class="mt-2 text-sm text-slate-900 font-semibold"><?= htmlspecialchars($lesson['title'] ?? '') ?></div>
        <div class="mt-3 text-xs uppercase tracking-wide text-slate-500">Summary</div>
        <div class="mt-2 text-sm text-slate-600 whitespace-pre-wrap"><?= htmlspecialchars($lesson['description'] ?? '—') ?></div>
      </div>
    </div>

    <div class="rounded-xl border border-slate-200 p-4">
      <div class="text-xs uppercase tracking-wide text-slate-500">Lesson Content</div>
      <div class="mt-3 text-sm text-slate-700 whitespace-pre-wrap"><?= htmlspecialchars($lesson['content'] ?? '') ?></div>
    </div>

    <?php if (!empty($lesson['url'])): ?>
      <div class="rounded-xl border border-slate-200 p-4">
        <div class="text-xs uppercase tracking-wide text-slate-500">Resources</div>
        <a href="<?= htmlspecialchars($lesson['url']) ?>" class="mt-2 inline-flex text-sm text-slate-900 underline" target="_blank" rel="noreferrer">
          <?= htmlspecialchars($lesson['url']) ?>
        </a>
      </div>
    <?php endif; ?>
  </div>
<?php
  $content = ob_get_clean();
  require __DIR__ . '/../layout.php';
?>
