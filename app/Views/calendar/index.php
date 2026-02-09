<?php
  $pageTitle = 'Calendar';
  $pageSubtitle = 'Academic year events and class highlights.';

  $calendarStart = $calendarStart ?? new DateTime('first day of this month');
  $calendarEnd = $calendarEnd ?? new DateTime('last day of this month');
  $monthStart = $monthStart ?? new DateTime('first day of this month');
  $calendarMap = $calendarMap ?? [];
  $monthLabel = $monthLabel ?? '';
  $monthPrev = $monthPrev ?? '';
  $monthNext = $monthNext ?? '';

  $rangeDays = $rangeDays ?? 14;
  $terms = $terms ?? [];
  $termFilter = $termFilter ?? 0;
  $myClassesOnly = !empty($myClassesOnly);
  $isAdmin = !empty($isAdmin);

  $queryBase = [
    'year_id' => $yearFilter ?? '',
    'class_id' => $classFilter ?? '',
    'term_id' => $termFilter ?? '',
    'category' => $category ?? '',
    'scope' => $scope ?? '',
    'q' => $q ?? '',
    'range_days' => $rangeDays,
    'my_classes' => $myClassesOnly ? 1 : 0,
  ];

  $prevQuery = http_build_query(array_filter(array_merge($queryBase, ['month' => $monthPrev])));
  $nextQuery = http_build_query(array_filter(array_merge($queryBase, ['month' => $monthNext])));

  $todayKey = (new DateTime('today'))->format('Y-m-d');

  ob_start();
?>
  <div class="flex flex-col gap-6">
    <div class="flex flex-wrap items-end justify-between gap-3">
      <form method="get" class="filter-bar grid flex-1 gap-3 md:grid-cols-4">
        <div>
          <label class="section-label">Year</label>
          <select name="year_id" class="mt-1.5 w-full rounded-xl border border-slate-200 px-3 py-2 text-sm" data-enhance="search">
            <option value="">All</option>
            <?php foreach ($years as $year): ?>
              <option value="<?= (int)$year['id'] ?>" <?= (string)$yearFilter === (string)$year['id'] ? 'selected' : '' ?>><?= htmlspecialchars($year['label']) ?></option>
            <?php endforeach; ?>
          </select>
        </div>
        <div>
          <label class="section-label">Class</label>
          <select name="class_id" class="mt-1.5 w-full rounded-xl border border-slate-200 px-3 py-2 text-sm" data-enhance="search">
            <option value="">All</option>
            <?php foreach ($classes as $class): ?>
              <option value="<?= (int)$class['id'] ?>" <?= (string)$classFilter === (string)$class['id'] ? 'selected' : '' ?>><?= htmlspecialchars($class['name']) ?></option>
            <?php endforeach; ?>
          </select>
        </div>
        <div>
          <label class="section-label">Term</label>
          <select name="term_id" class="mt-1.5 w-full rounded-xl border border-slate-200 px-3 py-2 text-sm" data-enhance="search">
            <option value="">All</option>
            <?php foreach ($terms as $term): ?>
              <option value="<?= (int)$term['id'] ?>" <?= (string)$termFilter === (string)$term['id'] ? 'selected' : '' ?>><?= htmlspecialchars($term['label']) ?></option>
            <?php endforeach; ?>
          </select>
        </div>
        <div>
          <label class="section-label">Category</label>
          <select name="category" class="mt-1.5 w-full rounded-xl border border-slate-200 px-3 py-2 text-sm" data-enhance="search">
            <option value="">All</option>
            <?php foreach ($categories as $cat): ?>
              <option value="<?= $cat ?>" <?= $category === $cat ? 'selected' : '' ?>><?= $cat ?></option>
            <?php endforeach; ?>
          </select>
        </div>
        <div>
          <label class="section-label">Scope</label>
          <select name="scope" class="mt-1.5 w-full rounded-xl border border-slate-200 px-3 py-2 text-sm" data-enhance="search">
            <option value="">All</option>
            <option value="GLOBAL" <?= $scope === 'GLOBAL' ? 'selected' : '' ?>>Global</option>
            <option value="CLASS" <?= $scope === 'CLASS' ? 'selected' : '' ?>>Class</option>
          </select>
        </div>
        <div>
          <label class="section-label">Search</label>
          <input name="q" value="<?= htmlspecialchars($q ?? '') ?>" placeholder="Title or description" class="mt-1.5 w-full rounded-xl border border-slate-200 px-3 py-2 text-sm">
        </div>
        <div>
          <label class="section-label">Upcoming</label>
          <select name="range_days" class="mt-1.5 w-full rounded-xl border border-slate-200 px-3 py-2 text-sm" data-enhance="search">
            <option value="14" <?= (int)$rangeDays === 14 ? 'selected' : '' ?>>Next 2 weeks</option>
            <option value="30" <?= (int)$rangeDays === 30 ? 'selected' : '' ?>>Next 30 days</option>
          </select>
        </div>
        <div class="flex items-end gap-3 md:col-span-4">
          <?php if (!$isAdmin): ?>
            <label class="inline-flex items-center gap-2 text-xs text-slate-500">
              <input type="checkbox" name="my_classes" value="1" <?= $myClassesOnly ? 'checked' : '' ?> class="rounded border-slate-300">
              My classes only
            </label>
          <?php endif; ?>
          <button class="btn btn-primary btn-sm">Filter</button>
          <a href="/calendar" class="btn btn-secondary btn-sm">Reset</a>
        </div>
      </form>

      <div class="flex items-center gap-2">
        <a href="/calendar/export?<?= htmlspecialchars(http_build_query(array_filter($queryBase))) ?>" class="btn btn-secondary btn-sm">
          <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/><polyline points="7 10 12 15 17 10"/><line x1="12" y1="15" x2="12" y2="3"/></svg>
          CSV
        </a>
        <a href="/calendar/ical?<?= htmlspecialchars(http_build_query(array_filter($queryBase))) ?>" class="btn btn-secondary btn-sm">
          <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="4" width="18" height="18" rx="2" ry="2"/><line x1="16" y1="2" x2="16" y2="6"/><line x1="8" y1="2" x2="8" y2="6"/><line x1="3" y1="10" x2="21" y2="10"/></svg>
          iCal
        </a>
        <?php if (!empty($isAdmin)): ?>
          <a href="/calendar/create" class="btn btn-primary btn-sm">
            <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/></svg>
            New Event
          </a>
        <?php endif; ?>
      </div>
    </div>

    <div class="grid gap-6 lg:grid-cols-[1.2fr_1fr]">
      <div class="rounded-2xl border border-slate-200 bg-white p-4">
        <div class="flex items-center justify-between">
          <div class="text-sm font-semibold text-slate-900"><?= htmlspecialchars($monthLabel) ?></div>
          <div class="flex items-center gap-2">
            <a class="btn btn-secondary btn-xs" href="/calendar?<?= htmlspecialchars($prevQuery) ?>">Prev</a>
            <a class="btn btn-secondary btn-xs" href="/calendar?<?= htmlspecialchars($nextQuery) ?>">Next</a>
          </div>
        </div>

        <div class="mt-4 grid grid-cols-7 text-xs text-slate-500">
          <?php foreach (['Sun','Mon','Tue','Wed','Thu','Fri','Sat'] as $dow): ?>
            <div class="px-2 py-1 text-center font-semibold uppercase tracking-wide"><?= $dow ?></div>
          <?php endforeach; ?>
        </div>

        <div class="grid grid-cols-7 gap-px overflow-hidden rounded-xl border border-slate-200 bg-slate-100 text-sm">
          <?php
            $cursor = clone $calendarStart;
            while ($cursor <= $calendarEnd):
              $key = $cursor->format('Y-m-d');
              $isCurrent = $cursor->format('Y-m') === $monthStart->format('Y-m');
              $isToday = $key === $todayKey;
              $eventsForDay = $calendarMap[$key] ?? [];
              $extraCount = max(0, count($eventsForDay) - 2);
          ?>
            <div class="min-h-[90px] cursor-pointer bg-white px-2 py-2 <?= $isCurrent ? '' : 'opacity-60' ?>"
                 data-calendar-day="<?= htmlspecialchars($key) ?>"
                 hx-get="/calendar/day?date=<?= htmlspecialchars($key) ?>&<?= htmlspecialchars(http_build_query(array_filter($queryBase))) ?>"
                 hx-target="[data-calendar-drawer-body]"
                 hx-swap="innerHTML">
              <div class="flex items-center justify-between">
                <span class="text-xs font-semibold <?= $isToday ? 'text-emerald-600' : 'text-slate-700' ?>"><?= $cursor->format('j') ?></span>
                <?php if ($isToday): ?>
                  <span class="rounded-full bg-emerald-100 px-2 py-0.5 text-[10px] font-semibold text-emerald-700">Today</span>
                <?php endif; ?>
              </div>
              <div class="mt-1 flex flex-col gap-1">
                <?php foreach (array_slice($eventsForDay, 0, 2) as $event): ?>
                  <div class="rounded-md bg-slate-100 px-2 py-1 text-[11px] text-slate-700">
                    <?= htmlspecialchars($event['title']) ?>
                  </div>
                <?php endforeach; ?>
                <?php if ($extraCount > 0): ?>
                  <div class="text-[10px] text-slate-400">+<?= $extraCount ?> more</div>
                <?php endif; ?>
              </div>
            </div>
          <?php
              $cursor->modify('+1 day');
            endwhile;
          ?>
        </div>
      </div>

      <div class="rounded-2xl border border-slate-200 bg-white p-4">
        <div class="flex items-center justify-between">
          <div>
            <div class="text-sm font-semibold text-slate-900">Upcoming (<?= (int)$rangeDays ?> days)</div>
            <div class="text-xs text-slate-500">Next highlights for your filters.</div>
          </div>
          <?php if (!empty($isAdmin)): ?>
            <a href="/calendar/create" class="btn btn-ghost btn-xs">Add event</a>
          <?php endif; ?>
        </div>
        <div class="mt-4 space-y-3">
          <?php if (empty($upcoming)): ?>
            <div class="text-sm text-slate-500">No upcoming events found.</div>
          <?php else: ?>
            <?php foreach ($upcoming as $event): ?>
              <?php
                $start = new DateTime($event['start_datetime']);
                $end = new DateTime($event['end_datetime']);
                $allDay = (int)$event['all_day'] === 1;
                $scopeLabel = $event['scope'] === 'CLASS' ? ('Class • ' . ($event['class_name'] ?? 'Unknown')) : 'Global';
                $teacherRows = $classTeachers[(int)($event['class_id'] ?? 0)] ?? [];
              ?>
              <div class="card-hover rounded-xl border border-slate-200 px-3 py-3">
                <div class="text-sm font-semibold text-slate-900"><?= htmlspecialchars($event['title']) ?></div>
                <div class="mt-1 flex items-center gap-2 text-xs text-slate-500">
                  <span><?= htmlspecialchars($scopeLabel) ?></span>
                  <span class="badge badge-neutral"><?= htmlspecialchars($event['category']) ?></span>
                </div>
                <?php if (!empty($teacherRows)): ?>
                  <div class="mt-1 text-xs text-slate-500">
                    <?php
                      $labels = [];
                      foreach ($teacherRows as $row) {
                        $role = ($row['assignment_role'] ?? '') === 'MAIN' ? 'Main' : 'Asst';
                        $labels[] = htmlspecialchars($row['full_name']) . ' (' . $role . ')';
                      }
                      echo implode(', ', $labels);
                    ?>
                  </div>
                <?php endif; ?>
                <div class="mt-2 text-xs text-slate-600">
                  <?= htmlspecialchars($start->format('d M Y')) ?>
                  <?php if ($allDay): ?>
                    <span class="text-slate-400">All day</span>
                  <?php else: ?>
                    <span class="text-slate-400"><?= htmlspecialchars($start->format('H:i')) ?></span>
                  <?php endif; ?>
                  <span class="text-slate-400">to</span>
                  <?= htmlspecialchars($end->format('d M Y')) ?><?= $allDay ? '' : ' ' . htmlspecialchars($end->format('H:i')) ?>
                </div>
                <?php if (!empty($isAdmin)): ?>
                  <a href="/calendar/<?= (int)$event['id'] ?>/edit" class="mt-2 inline-flex text-xs text-slate-500 underline">Edit</a>
                <?php endif; ?>
              </div>
            <?php endforeach; ?>
          <?php endif; ?>
        </div>
      </div>
    </div>

    <div class="table-wrap overflow-x-auto rounded-2xl border border-slate-200">
      <table class="cdm-table w-full text-left text-sm">
        <thead class="bg-slate-50 text-xs uppercase tracking-wide text-slate-500">
          <tr>
            <th class="px-4 py-3">Event</th>
            <th class="px-4 py-3">Dates</th>
            <th class="px-4 py-3">Scope</th>
            <th class="px-4 py-3">Category</th>
            <th class="px-4 py-3">Actions</th>
          </tr>
        </thead>
        <tbody>
          <?php if (empty($events)): ?>
            <tr>
              <td colspan="5" class="px-4 py-6">
                <?php
                  $message = 'No calendar events found.';
                  if (!empty($isAdmin)) {
                    $actionLabel = 'New Event';
                    $actionHref = '/calendar/create';
                  }
                ?>
                <?php require __DIR__ . '/../partials/empty.php'; ?>
              </td>
            </tr>
          <?php else: ?>
            <?php foreach ($events as $event): ?>
              <?php
                $start = new DateTime($event['start_datetime']);
                $end = new DateTime($event['end_datetime']);
                $allDay = (int)$event['all_day'] === 1;
                $scopeLabel = $event['scope'] === 'CLASS' ? ('Class • ' . ($event['class_name'] ?? 'Unknown')) : 'Global';
                $teacherRows = $classTeachers[(int)($event['class_id'] ?? 0)] ?? [];
              ?>
              <tr class="border-t border-slate-200">
                <td class="px-4 py-3">
                  <div class="font-semibold text-slate-900"><?= htmlspecialchars($event['title']) ?></div>
                  <div class="text-xs text-slate-500">Year: <?= htmlspecialchars($event['year_label'] ?? '') ?></div>
                </td>
                <td class="px-4 py-3 text-slate-600">
                  <?= htmlspecialchars($start->format('d M Y')) ?>
                  <?php if (!$allDay): ?>
                    <span class="text-xs text-slate-400"><?= htmlspecialchars($start->format('H:i')) ?></span>
                  <?php else: ?>
                    <span class="text-xs text-slate-400">All day</span>
                  <?php endif; ?>
                  <div class="text-xs text-slate-400">to <?= htmlspecialchars($end->format('d M Y')) ?><?= $allDay ? '' : ' ' . htmlspecialchars($end->format('H:i')) ?></div>
                </td>
                <td class="px-4 py-3 text-slate-600">
                  <div><?= htmlspecialchars($scopeLabel) ?></div>
                  <?php if (!empty($teacherRows)): ?>
                    <div class="mt-1 text-xs text-slate-500">
                      <?php
                        $labels = [];
                        foreach ($teacherRows as $row) {
                          $role = ($row['assignment_role'] ?? '') === 'MAIN' ? 'Main' : 'Asst';
                          $labels[] = htmlspecialchars($row['full_name']) . ' (' . $role . ')';
                        }
                        echo implode(', ', $labels);
                      ?>
                    </div>
                  <?php endif; ?>
                </td>
                <td class="px-4 py-3"><span class="badge badge-neutral"><?= htmlspecialchars($event['category']) ?></span></td>
                <td class="px-4 py-3">
                  <?php if (!empty($isAdmin)): ?>
                    <a href="/calendar/<?= (int)$event['id'] ?>/edit" class="btn btn-secondary btn-xs">Edit</a>
                  <?php else: ?>
                    <span class="text-xs text-slate-400">View only</span>
                  <?php endif; ?>
                </td>
              </tr>
            <?php endforeach; ?>
          <?php endif; ?>
        </tbody>
      </table>
    </div>
  </div>

  <div class="fixed inset-0 z-40 hidden bg-slate-900/40" data-calendar-overlay></div>
  <div class="fixed right-0 top-0 z-50 h-full w-full max-w-md translate-x-full bg-white shadow-2xl transition-transform" data-calendar-drawer>
    <div class="flex items-center justify-between border-b border-slate-200 px-4 py-4">
      <div class="text-sm font-semibold text-slate-900" data-calendar-drawer-title></div>
      <button type="button" class="btn btn-ghost btn-sm" data-calendar-drawer-close>Close</button>
    </div>
    <div class="flex flex-col gap-3 px-4 py-4" data-calendar-drawer-body></div>
  </div>
  <script>
    window.CDM_CALENDAR_DRAWER = true;
  </script>
<?php
  $content = ob_get_clean();
  require __DIR__ . '/../layout.php';
?>
