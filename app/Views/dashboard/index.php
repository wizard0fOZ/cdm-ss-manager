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
  <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-4">
    <div class="stat-card rounded-2xl border border-slate-200 bg-white p-5">
      <div class="flex items-start justify-between">
        <div>
          <div class="section-label">Students</div>
          <div class="mt-2 text-3xl font-bold text-slate-900"><?= (int)$studentsCount ?></div>
          <div class="mt-1 text-xs text-slate-400">Active roster count</div>
        </div>
        <div class="stat-card-icon bg-blue-50 text-blue-600">
          <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round">
            <path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M23 21v-2a4 4 0 0 0-3-3.87"/><path d="M16 3.13a4 4 0 0 1 0 7.75"/>
          </svg>
        </div>
      </div>
    </div>
    <div class="stat-card rounded-2xl border border-slate-200 bg-white p-5">
      <div class="flex items-start justify-between">
        <div>
          <div class="section-label">Classes</div>
          <div class="mt-2 text-3xl font-bold text-slate-900"><?= (int)$classesCount ?></div>
          <div class="mt-1 text-xs text-slate-400">Current academic year</div>
        </div>
        <div class="stat-card-icon bg-emerald-50 text-emerald-600">
          <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round">
            <rect x="2" y="3" width="20" height="14" rx="2" ry="2"/><line x1="8" y1="21" x2="16" y2="21"/><line x1="12" y1="17" x2="12" y2="21"/>
          </svg>
        </div>
      </div>
    </div>
    <div class="stat-card rounded-2xl border border-slate-200 bg-white p-5">
      <div class="flex items-start justify-between">
        <div>
          <div class="section-label">Teachers</div>
          <div class="mt-2 text-3xl font-bold text-slate-900"><?= (int)$teachersCount ?></div>
          <div class="mt-1 text-xs text-slate-400">Active catechists</div>
        </div>
        <div class="stat-card-icon bg-purple-50 text-purple-600">
          <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round">
            <path d="M20 21a8 8 0 0 0-16 0"/><circle cx="12" cy="7" r="4"/>
          </svg>
        </div>
      </div>
    </div>
    <div class="stat-card rounded-2xl border border-slate-200 bg-white p-5">
      <div class="flex items-start justify-between">
        <div>
          <div class="section-label">Active Year</div>
          <div class="mt-2 text-xl font-bold text-slate-900"><?= htmlspecialchars($activeYear['label'] ?? 'Not set') ?></div>
          <div class="mt-1 text-xs text-slate-400">System Settings</div>
        </div>
        <div class="stat-card-icon bg-amber-50 text-amber-600">
          <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round">
            <rect x="3" y="4" width="18" height="18" rx="2" ry="2"/><line x1="16" y1="2" x2="16" y2="6"/><line x1="8" y1="2" x2="8" y2="6"/><line x1="3" y1="10" x2="21" y2="10"/>
          </svg>
        </div>
      </div>
    </div>
  </div>

  <div class="mt-6 grid gap-4 lg:grid-cols-3">
    <div class="rounded-2xl border border-slate-200 bg-white p-5 lg:col-span-2">
      <div class="flex items-center justify-between">
        <div>
          <div class="text-sm font-semibold text-slate-900">Upcoming Events</div>
          <div class="mt-0.5 text-xs text-slate-400">Next 2 weeks of key dates.</div>
        </div>
        <a href="/calendar" class="btn btn-ghost btn-xs text-slate-500">View all</a>
      </div>
      <div class="mt-4 space-y-2">
        <?php if (empty($upcoming)): ?>
          <div class="rounded-xl bg-slate-50 px-4 py-6 text-center text-sm text-slate-400">No events scheduled.</div>
        <?php else: ?>
          <?php foreach ($upcoming as $event): ?>
            <?php
              $start = new DateTime($event['start_datetime']);
              $end = new DateTime($event['end_datetime']);
              $allDay = (int)$event['all_day'] === 1;
              $scopeLabel = $event['scope'] === 'CLASS' ? ('Class' . ($event['class_name'] ? ' · ' . $event['class_name'] : '')) : 'Global';
            ?>
            <div class="card-hover flex items-start gap-3 rounded-xl border border-slate-100 px-4 py-3">
              <div class="flex h-10 w-10 flex-shrink-0 flex-col items-center justify-center rounded-lg bg-slate-50 text-slate-600">
                <span class="text-[10px] font-semibold uppercase leading-none"><?= htmlspecialchars($start->format('M')) ?></span>
                <span class="text-sm font-bold leading-tight"><?= htmlspecialchars($start->format('j')) ?></span>
              </div>
              <div class="min-w-0 flex-1">
                <div class="text-sm font-semibold text-slate-900"><?= htmlspecialchars($event['title']) ?></div>
                <div class="mt-0.5 flex flex-wrap items-center gap-2 text-xs text-slate-400">
                  <span><?= htmlspecialchars($scopeLabel) ?></span>
                  <span>&middot;</span>
                  <span><?= htmlspecialchars($event['category']) ?></span>
                  <span>&middot;</span>
                  <span><?= $allDay ? 'All day' : htmlspecialchars($start->format('H:i') . ' – ' . $end->format('H:i')) ?></span>
                </div>
              </div>
            </div>
          <?php endforeach; ?>
        <?php endif; ?>
      </div>
    </div>
    <div class="rounded-2xl border border-slate-200 bg-white p-5">
      <div class="flex items-center justify-between">
        <div>
          <div class="text-sm font-semibold text-slate-900">Announcements</div>
          <div class="mt-0.5 text-xs text-slate-400">Latest notices.</div>
        </div>
        <a href="/announcements" class="btn btn-ghost btn-xs text-slate-500">View all</a>
      </div>
      <div class="mt-4 space-y-2">
        <?php if (empty($announcements)): ?>
          <div class="rounded-xl bg-slate-50 px-4 py-6 text-center text-sm text-slate-400">No active announcements.</div>
        <?php else: ?>
          <?php foreach ($announcements as $item): ?>
            <?php $scopeLabel = $item['scope'] === 'CLASS' ? ('Class' . (!empty($item['class_name']) ? ' · ' . $item['class_name'] : '')) : 'Global'; ?>
            <div class="card-hover rounded-xl border border-slate-100 px-4 py-3">
              <div class="text-sm font-semibold text-slate-900"><?= htmlspecialchars($item['title']) ?></div>
              <div class="mt-0.5 text-xs text-slate-400"><?= htmlspecialchars($scopeLabel) ?></div>
              <div class="mt-1.5 text-xs text-slate-500 line-clamp-2"><?= htmlspecialchars($item['message']) ?></div>
            </div>
          <?php endforeach; ?>
        <?php endif; ?>
      </div>
    </div>
  </div>

  <div class="mt-6 grid gap-4 md:grid-cols-2">
    <div class="stat-card rounded-2xl border border-slate-200 bg-white p-5">
      <div class="flex items-start justify-between">
        <div>
          <div class="section-label">Attendance (last 4 Sundays)</div>
          <div class="mt-2 text-3xl font-bold text-slate-900">
            <?= $attendanceRate !== null ? htmlspecialchars((string)$attendanceRate) . '%' : '—' ?>
          </div>
          <div class="mt-1 text-xs text-slate-400">Based on marked records.</div>
        </div>
        <div class="stat-card-icon bg-emerald-50 text-emerald-600">
          <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round">
            <path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"/><path d="M22 4 12 14.01l-3-3"/>
          </svg>
        </div>
      </div>
    </div>
    <div class="stat-card rounded-2xl border border-slate-200 bg-white p-5">
      <div class="flex items-start justify-between">
        <div>
          <div class="section-label">Pending Lesson Plans</div>
          <div class="mt-2 text-3xl font-bold text-slate-900"><?= (int)$pendingLessons ?></div>
          <div class="mt-1 text-xs text-slate-400">Draft lessons in next 2 weeks.</div>
        </div>
        <div class="stat-card-icon bg-amber-50 text-amber-600">
          <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round">
            <path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><path d="M14 2v6h6"/><line x1="16" y1="13" x2="8" y2="13"/><line x1="16" y1="17" x2="8" y2="17"/>
          </svg>
        </div>
      </div>
    </div>
  </div>

  <div class="mt-6 rounded-2xl border border-slate-200 bg-white p-5">
    <div class="text-sm font-semibold text-slate-900">Quick Actions</div>
    <div class="mt-3 flex flex-wrap gap-2">
      <a href="/attendance" class="btn btn-secondary btn-sm">
        <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"/><path d="M22 4 12 14.01l-3-3"/></svg>
        Take Attendance
      </a>
      <a href="/lessons" class="btn btn-secondary btn-sm">
        <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><path d="M14 2v6h6"/></svg>
        Lesson Plans
      </a>
      <a href="/reports" class="btn btn-primary btn-sm">
        <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="3" width="7" height="9"/><rect x="14" y="3" width="7" height="5"/><rect x="14" y="12" width="7" height="9"/><rect x="3" y="16" width="7" height="5"/></svg>
        Reports
      </a>
    </div>
  </div>
<?php
  $content = ob_get_clean();
  require __DIR__ . '/../layout.php';
?>
