<?php
  $pageTitle = 'Dashboard';
  $pageSubtitle = 'A quick overview of students, classes, and upcoming activities.';

  $studentsCount = $studentsCount ?? 0;
  $classesCount = $classesCount ?? 0;
  $teachersCount = $teachersCount ?? 0;
  $activeYear = $activeYear ?? null;
  $announcements = $announcements ?? [];
  $attendanceRate = $attendanceRate ?? null;
  $pendingLessons = $pendingLessons ?? 0;

  ob_start();
?>
  <div class="grid gap-4 md:grid-cols-4">
    <div class="rounded-2xl border border-slate-200 bg-white p-4">
      <div class="text-xs uppercase tracking-wide text-slate-500">Students</div>
      <div class="mt-2 text-2xl font-semibold text-slate-900"><?= (int)$studentsCount ?></div>
      <div class="mt-1 text-xs text-slate-500">Active roster count</div>
    </div>
    <div class="rounded-2xl border border-slate-200 bg-white p-4">
      <div class="text-xs uppercase tracking-wide text-slate-500">Classes</div>
      <div class="mt-2 text-2xl font-semibold text-slate-900"><?= (int)$classesCount ?></div>
      <div class="mt-1 text-xs text-slate-500">Current academic year</div>
    </div>
    <div class="rounded-2xl border border-slate-200 bg-white p-4">
      <div class="text-xs uppercase tracking-wide text-slate-500">Teachers</div>
      <div class="mt-2 text-2xl font-semibold text-slate-900"><?= (int)$teachersCount ?></div>
      <div class="mt-1 text-xs text-slate-500">Active catechists</div>
    </div>
    <div class="rounded-2xl border border-slate-200 bg-slate-50 p-4">
      <div class="text-xs uppercase tracking-wide text-slate-500">Active Year</div>
      <div class="mt-2 text-lg font-semibold text-slate-900"><?= htmlspecialchars($activeYear['label'] ?? 'Not set') ?></div>
      <div class="mt-1 text-xs text-slate-500">Update in System Settings</div>
    </div>
  </div>

  <div class="mt-6 grid gap-4 lg:grid-cols-3">
    <div class="rounded-2xl border border-slate-200 bg-white p-5 lg:col-span-2">
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
    <div class="rounded-2xl border border-slate-200 bg-white p-5">
      <div class="text-sm font-semibold text-slate-900">Announcements</div>
      <div class="mt-1 text-xs text-slate-500">Latest notices and updates.</div>
      <div class="mt-4 space-y-3">
        <?php if (empty($announcements)): ?>
          <div class="text-sm text-slate-500">No active announcements.</div>
        <?php else: ?>
          <?php foreach ($announcements as $item): ?>
            <?php $scopeLabel = $item['scope'] === 'CLASS' ? ('Class • ' . ($item['class_name'] ?? '')) : 'Global'; ?>
            <div class="rounded-lg border border-slate-200 px-3 py-2">
              <div class="text-sm font-semibold text-slate-900"><?= htmlspecialchars($item['title']) ?></div>
              <div class="text-xs text-slate-500"><?= htmlspecialchars($scopeLabel) ?></div>
              <div class="mt-1 text-xs text-slate-600"><?= htmlspecialchars($item['message']) ?></div>
            </div>
          <?php endforeach; ?>
        <?php endif; ?>
      </div>
      <a href="/announcements" class="mt-4 inline-flex text-xs text-slate-500 underline">View announcements</a>
    </div>
  </div>

  <div class="mt-6 grid gap-4 md:grid-cols-2">
    <div class="rounded-2xl border border-slate-200 bg-white p-5">
      <div class="text-xs uppercase tracking-wide text-slate-500">Attendance (last 4 Sundays)</div>
      <div class="mt-2 text-2xl font-semibold text-slate-900">
        <?= $attendanceRate !== null ? htmlspecialchars((string)$attendanceRate) . '%' : '—' ?>
      </div>
      <div class="mt-1 text-xs text-slate-500">Based on marked attendance records.</div>
    </div>
    <div class="rounded-2xl border border-slate-200 bg-white p-5">
      <div class="text-xs uppercase tracking-wide text-slate-500">Pending Lesson Plans</div>
      <div class="mt-2 text-2xl font-semibold text-slate-900"><?= (int)$pendingLessons ?></div>
      <div class="mt-1 text-xs text-slate-500">Draft lessons in next 2 weeks.</div>
    </div>
  </div>

  <div class="mt-6 rounded-2xl border border-slate-200 bg-white p-5">
    <div class="text-sm font-semibold text-slate-900">Quick Actions</div>
    <div class="mt-3 flex flex-wrap gap-2">
      <a href="/attendance" class="inline-flex items-center justify-center rounded-lg border border-slate-200 px-4 py-2 text-sm">Take Attendance</a>
      <a href="/lessons" class="inline-flex items-center justify-center rounded-lg border border-slate-200 px-4 py-2 text-sm">Lesson Plans</a>
      <a href="/reports" class="inline-flex items-center justify-center rounded-lg bg-slate-900 px-4 py-2 text-sm font-semibold text-white">Reports</a>
    </div>
  </div>
<?php
  $content = ob_get_clean();
  require __DIR__ . '/../layout.php';
?>
