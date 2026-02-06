<?php
  $pageTitle = 'Dashboard';
  $pageSubtitle = 'Today’s snapshot of Sunday School operations.';

  ob_start();
?>
  <div class="grid gap-4 md:grid-cols-3">
    <div class="rounded-xl border border-slate-200 bg-slate-50 p-4">
      <div class="text-xs uppercase tracking-wide text-slate-500">Students</div>
      <div class="mt-2 text-2xl font-semibold text-slate-900">0</div>
      <div class="mt-1 text-xs text-slate-500">Awaiting student module</div>
    </div>
    <div class="rounded-xl border border-slate-200 bg-slate-50 p-4">
      <div class="text-xs uppercase tracking-wide text-slate-500">Classes</div>
      <div class="mt-2 text-2xl font-semibold text-slate-900">0</div>
      <div class="mt-1 text-xs text-slate-500">Set in Phase 4</div>
    </div>
    <div class="rounded-xl border border-slate-200 bg-slate-50 p-4">
      <div class="text-xs uppercase tracking-wide text-slate-500">Attendance</div>
      <div class="mt-2 text-2xl font-semibold text-slate-900">0%</div>
      <div class="mt-1 text-xs text-slate-500">Next: Phase 5</div>
    </div>
  </div>

  <div class="mt-6 grid gap-4 lg:grid-cols-2">
    <div class="rounded-xl border border-slate-200 bg-white p-5">
      <div class="text-sm font-semibold text-slate-900">Upcoming (next 2 weeks)</div>
      <div class="mt-1 text-xs text-slate-500">Key dates and class events.</div>
      <div class="mt-4 space-y-3">
        <?php if (empty($upcoming)): ?>
          <div class="text-sm text-slate-500">No events scheduled.</div>
        <?php else: ?>
          <?php foreach ($upcoming as $event): ?>
            <?php
              $start = new DateTime($event['start_datetime']);
              $end = new DateTime($event['end_datetime']);
              $allDay = (int)$event['all_day'] === 1;
              $scopeLabel = $event['scope'] === 'CLASS' ? ('Class • ' . ($event['class_name'] ?? 'Unknown')) : 'Global';
            ?>
            <div class="rounded-lg border border-slate-200 px-3 py-2">
              <div class="text-sm font-semibold text-slate-900"><?= htmlspecialchars($event['title']) ?></div>
              <div class="text-xs text-slate-500"><?= htmlspecialchars($scopeLabel) ?> • <?= htmlspecialchars($event['category']) ?></div>
              <div class="mt-1 text-xs text-slate-600">
                <?= htmlspecialchars($start->format('d M Y')) ?>
                <?php if ($allDay): ?>
                  <span class="text-slate-400">All day</span>
                <?php else: ?>
                  <span class="text-slate-400"><?= htmlspecialchars($start->format('H:i')) ?></span>
                <?php endif; ?>
                <span class="text-slate-400">to</span>
                <?= htmlspecialchars($end->format('d M Y')) ?><?= $allDay ? '' : ' ' . htmlspecialchars($end->format('H:i')) ?>
              </div>
            </div>
          <?php endforeach; ?>
        <?php endif; ?>
      </div>
      <a href="/calendar" class="mt-4 inline-flex text-xs text-slate-500 underline">View full calendar</a>
    </div>
    <div class="rounded-xl border border-slate-200 bg-white p-5">
      <div class="text-sm font-semibold text-slate-900">Next Up</div>
      <p class="mt-2 text-sm text-slate-600">Check announcements and publish lesson plans for your classes.</p>
      <div class="mt-4 flex flex-wrap gap-2">
        <a href="/announcements" class="inline-flex items-center justify-center rounded-lg border border-slate-200 px-4 py-2 text-sm">Announcements</a>
        <a href="/lessons" class="inline-flex items-center justify-center rounded-lg bg-slate-900 px-4 py-2 text-sm font-semibold text-white">Lessons</a>
      </div>
    </div>
  </div>
<?php
  $content = ob_get_clean();
  require __DIR__ . '/../layout.php';
?>
