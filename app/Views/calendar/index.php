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
      <form method="get" class="grid gap-3 md:grid-cols-7">
        <div>
          <label class="text-xs text-slate-500">Year</label>
          <select name="year_id" class="mt-1 w-full rounded-xl border border-slate-200 px-3 py-2 text-sm" data-enhance="search">
            <option value="">All</option>
            <?php foreach ($years as $year): ?>
              <option value="<?= (int)$year['id'] ?>" <?= (string)$yearFilter === (string)$year['id'] ? 'selected' : '' ?>><?= htmlspecialchars($year['label']) ?></option>
            <?php endforeach; ?>
          </select>
        </div>
        <div>
          <label class="text-xs text-slate-500">Class</label>
          <select name="class_id" class="mt-1 w-full rounded-xl border border-slate-200 px-3 py-2 text-sm" data-enhance="search">
            <option value="">All</option>
            <?php foreach ($classes as $class): ?>
              <option value="<?= (int)$class['id'] ?>" <?= (string)$classFilter === (string)$class['id'] ? 'selected' : '' ?>><?= htmlspecialchars($class['name']) ?></option>
            <?php endforeach; ?>
          </select>
        </div>
        <div>
          <label class="text-xs text-slate-500">Term</label>
          <select name="term_id" class="mt-1 w-full rounded-xl border border-slate-200 px-3 py-2 text-sm" data-enhance="search">
            <option value="">All</option>
            <?php foreach ($terms as $term): ?>
              <option value="<?= (int)$term['id'] ?>" <?= (string)$termFilter === (string)$term['id'] ? 'selected' : '' ?>><?= htmlspecialchars($term['label']) ?></option>
            <?php endforeach; ?>
          </select>
        </div>
        <div>
          <label class="text-xs text-slate-500">Category</label>
          <select name="category" class="mt-1 w-full rounded-xl border border-slate-200 px-3 py-2 text-sm" data-enhance="search">
            <option value="">All</option>
            <?php foreach ($categories as $cat): ?>
              <option value="<?= $cat ?>" <?= $category === $cat ? 'selected' : '' ?>><?= $cat ?></option>
            <?php endforeach; ?>
          </select>
        </div>
        <div>
          <label class="text-xs text-slate-500">Scope</label>
          <select name="scope" class="mt-1 w-full rounded-xl border border-slate-200 px-3 py-2 text-sm" data-enhance="search">
            <option value="">All</option>
            <option value="GLOBAL" <?= $scope === 'GLOBAL' ? 'selected' : '' ?>>Global</option>
            <option value="CLASS" <?= $scope === 'CLASS' ? 'selected' : '' ?>>Class</option>
          </select>
        </div>
        <div>
          <label class="text-xs text-slate-500">Search</label>
          <input name="q" value="<?= htmlspecialchars($q ?? '') ?>" placeholder="Title or description" class="mt-1 w-full rounded-xl border border-slate-200 px-3 py-2 text-sm">
        </div>
        <div>
          <label class="text-xs text-slate-500">Upcoming</label>
          <select name="range_days" class="mt-1 w-full rounded-xl border border-slate-200 px-3 py-2 text-sm" data-enhance="search">
            <option value="14" <?= (int)$rangeDays === 14 ? 'selected' : '' ?>>Next 2 weeks</option>
            <option value="30" <?= (int)$rangeDays === 30 ? 'selected' : '' ?>>Next 30 days</option>
          </select>
        </div>
        <div class="flex items-end gap-3 md:col-span-7">
          <?php if (!$isAdmin): ?>
            <label class="inline-flex items-center gap-2 text-xs text-slate-600">
              <input type="checkbox" name="my_classes" value="1" <?= $myClassesOnly ? 'checked' : '' ?>>
              My classes only
            </label>
          <?php endif; ?>
          <button class="rounded-xl bg-slate-900 px-4 py-2 text-sm font-semibold text-white">Filter</button>
          <a href="/calendar" class="rounded-xl border border-slate-200 px-4 py-2 text-sm">Reset</a>
        </div>
      </form>

      <div class="flex items-center gap-2">
        <a href="/calendar/export?<?= htmlspecialchars(http_build_query(array_filter($queryBase))) ?>" class="inline-flex items-center justify-center rounded-xl border border-slate-200 px-4 py-2 text-sm">Export CSV</a>
        <a href="/calendar/ical?<?= htmlspecialchars(http_build_query(array_filter($queryBase))) ?>" class="inline-flex items-center justify-center rounded-xl border border-slate-200 px-4 py-2 text-sm">Export iCal</a>
        <?php if (!empty($isAdmin)): ?>
          <a href="/calendar/create" class="inline-flex items-center justify-center rounded-xl bg-slate-900 px-4 py-2 text-sm font-semibold text-white">New Event</a>
        <?php endif; ?>
      </div>
    </div>

    <div class="grid gap-6 lg:grid-cols-[1.2fr_1fr]">
      <div class="rounded-2xl border border-slate-200 bg-white p-4">
        <div class="flex items-center justify-between">
          <div class="text-sm font-semibold text-slate-900"><?= htmlspecialchars($monthLabel) ?></div>
          <div class="flex items-center gap-2">
            <a class="rounded-lg border border-slate-200 px-3 py-1 text-xs" href="/calendar?<?= htmlspecialchars($prevQuery) ?>">Prev</a>
            <a class="rounded-lg border border-slate-200 px-3 py-1 text-xs" href="/calendar?<?= htmlspecialchars($nextQuery) ?>">Next</a>
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
            <div class="min-h-[90px] cursor-pointer bg-white px-2 py-2 <?= $isCurrent ? '' : 'opacity-60' ?>" data-calendar-day="<?= htmlspecialchars($key) ?>">
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
            <a href="/calendar/create" class="text-xs text-slate-500 underline">Add event</a>
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
              <div class="rounded-xl border border-slate-200 px-3 py-3">
                <div class="text-sm font-semibold text-slate-900"><?= htmlspecialchars($event['title']) ?></div>
                <div class="text-xs text-slate-500"><?= htmlspecialchars($scopeLabel) ?> • <?= htmlspecialchars($event['category']) ?></div>
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

    <div class="overflow-hidden rounded-2xl border border-slate-200">
      <table class="w-full text-left text-sm">
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
                <?php $message = 'No calendar events found.'; ?>
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
                <td class="px-4 py-3 text-slate-600"><?= htmlspecialchars($event['category']) ?></td>
                <td class="px-4 py-3">
                  <?php if (!empty($isAdmin)): ?>
                    <a href="/calendar/<?= (int)$event['id'] ?>/edit" class="rounded-lg border border-slate-200 px-3 py-1 text-xs">Edit</a>
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
      <button type="button" class="text-sm text-slate-500" data-calendar-drawer-close>Close</button>
    </div>
    <div class="flex flex-col gap-3 px-4 py-4" data-calendar-drawer-body></div>
  </div>

  <?php
    $calendarDays = [];
    foreach ($calendarMap as $date => $events) {
      foreach ($events as $event) {
        $teacherRows = $classTeachers[(int)($event['class_id'] ?? 0)] ?? [];
        $teacherLabels = '';
        if ($teacherRows) {
          $labels = [];
          foreach ($teacherRows as $row) {
            $role = ($row['assignment_role'] ?? '') === 'MAIN' ? 'Main' : 'Asst';
            $labels[] = $row['full_name'] . ' (' . $role . ')';
          }
          $teacherLabels = implode(', ', $labels);
        }
        $calendarDays[$date][] = [
          'title' => $event['title'],
          'category' => $event['category'],
          'scope' => $event['scope'],
          'class_name' => $event['class_name'] ?? '',
          'teachers' => $teacherLabels,
          'all_day' => (int)$event['all_day'] === 1,
          'start_time' => substr($event['start_datetime'], 11, 5),
          'end_time' => substr($event['end_datetime'], 11, 5),
        ];
      }
    }
    $calendarJson = json_encode($calendarDays, JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT);
  ?>
  <script>
    window.CDM_CALENDAR_DRAWER = true;
    window.CDM_CALENDAR_DAYS = <?= $calendarJson ?>;
  </script>
<?php
  $content = ob_get_clean();
  require __DIR__ . '/../layout.php';
?>
