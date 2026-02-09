<?php
  $date = $date ?? '';
  $events = $events ?? [];
  $classTeachers = $classTeachers ?? [];
?>
<div class="flex flex-col gap-3">
  <?php if (empty($events)): ?>
    <div class="text-sm text-slate-500">No events for this date.</div>
  <?php else: ?>
    <?php foreach ($events as $event): ?>
      <?php
        $start = new DateTime($event['start_datetime']);
        $end = new DateTime($event['end_datetime']);
        $allDay = (int)$event['all_day'] === 1;
        $scopeLabel = $event['scope'] === 'CLASS' ? ('Class • ' . ($event['class_name'] ?? 'Unknown')) : 'Global';
        $teacherRows = $classTeachers[(int)($event['class_id'] ?? 0)] ?? [];
      ?>
      <div class="rounded-lg border border-slate-200 px-3 py-2">
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
