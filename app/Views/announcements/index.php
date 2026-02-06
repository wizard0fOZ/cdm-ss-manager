<?php
  $pageTitle = 'Announcements';
  $pageSubtitle = 'Central updates for staff and classes.';

  $now = new DateTime('now');
  $statusFilter = $statusFilter ?? '';
  $showExpired = !empty($showExpired);
  $isAdmin = !empty($isAdmin);

  ob_start();
?>
  <div class="flex flex-col gap-4">
    <div class="flex flex-wrap items-end justify-between gap-3">
      <form method="get" class="grid gap-3 md:grid-cols-5">
        <div>
          <label class="text-xs text-slate-500">Scope</label>
          <select name="scope" class="mt-1 w-full rounded-xl border border-slate-200 px-3 py-2 text-sm" data-enhance="search">
            <option value="">All</option>
            <option value="GLOBAL" <?= $scope === 'GLOBAL' ? 'selected' : '' ?>>Global</option>
            <option value="CLASS" <?= $scope === 'CLASS' ? 'selected' : '' ?>>Class</option>
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
          <label class="text-xs text-slate-500">Window</label>
          <select name="status" class="mt-1 w-full rounded-xl border border-slate-200 px-3 py-2 text-sm" data-enhance="search">
            <option value="">All</option>
            <option value="ACTIVE" <?= $statusFilter === 'ACTIVE' ? 'selected' : '' ?>>Active</option>
            <option value="SCHEDULED" <?= $statusFilter === 'SCHEDULED' ? 'selected' : '' ?>>Scheduled</option>
            <option value="EXPIRED" <?= $statusFilter === 'EXPIRED' ? 'selected' : '' ?>>Expired</option>
            <?php if ($isAdmin): ?>
              <option value="DRAFT" <?= $statusFilter === 'DRAFT' ? 'selected' : '' ?>>Draft</option>
              <option value="PUBLISHED" <?= $statusFilter === 'PUBLISHED' ? 'selected' : '' ?>>Published</option>
            <?php endif; ?>
          </select>
        </div>
        <div>
          <label class="text-xs text-slate-500">Search</label>
          <input name="q" value="<?= htmlspecialchars($q ?? '') ?>" placeholder="Title or message" class="mt-1 w-full rounded-xl border border-slate-200 px-3 py-2 text-sm">
        </div>
        <div class="flex items-end gap-3 md:col-span-5">
          <label class="inline-flex items-center gap-2 text-xs text-slate-600">
            <input type="checkbox" name="show_expired" value="1" <?= $showExpired ? 'checked' : '' ?>>
            Show expired
          </label>
          <button class="rounded-xl bg-slate-900 px-4 py-2 text-sm font-semibold text-white">Filter</button>
          <a href="/announcements" class="rounded-xl border border-slate-200 px-4 py-2 text-sm">Reset</a>
        </div>
      </form>

      <?php if ($isAdmin): ?>
        <a href="/announcements/create" class="inline-flex items-center justify-center rounded-xl bg-slate-900 px-4 py-2 text-sm font-semibold text-white">New Announcement</a>
      <?php endif; ?>
    </div>

    <div class="overflow-hidden rounded-2xl border border-slate-200">
      <table class="w-full text-left text-sm">
        <thead class="bg-slate-50 text-xs uppercase tracking-wide text-slate-500">
          <tr>
            <th class="px-4 py-3">Title</th>
            <th class="px-4 py-3">Scope</th>
            <th class="px-4 py-3">Window</th>
            <th class="px-4 py-3">Status</th>
            <th class="px-4 py-3">Actions</th>
          </tr>
        </thead>
        <tbody>
          <?php if (empty($items)): ?>
            <tr>
              <td colspan="5" class="px-4 py-6">
                <?php $message = 'No announcements found.'; ?>
                <?php require __DIR__ . '/../partials/empty.php'; ?>
              </td>
            </tr>
          <?php else: ?>
            <?php foreach ($items as $item): ?>
              <?php
                $start = new DateTime($item['start_at']);
                $end = new DateTime($item['end_at']);
                $pinUntil = !empty($item['pin_until']) ? new DateTime($item['pin_until']) : null;
                $isPinnedActive = !empty($item['is_pinned']) && (!$pinUntil || $pinUntil >= $now);
                if ($now < $start) {
                  $status = 'Scheduled';
                  $badge = 'bg-blue-100 text-blue-700';
                } elseif ($now > $end) {
                  $status = 'Expired';
                  $badge = 'bg-slate-100 text-slate-600';
                } else {
                  $status = 'Active';
                  $badge = 'bg-emerald-100 text-emerald-700';
                }
                $scopeLabel = $item['scope'] === 'CLASS' ? ('Class • ' . ($item['class_name'] ?? 'Unknown')) : 'Global';
                $priority = (int)($item['priority'] ?? 0);
                $priorityLabel = $priority === 2 ? 'Urgent' : ($priority === 1 ? 'High' : 'Normal');
                $statusLabel = $item['status'] ?? 'PUBLISHED';
              ?>
              <tr class="border-t border-slate-200">
                <td class="px-4 py-3">
                  <div class="flex flex-wrap items-center gap-2">
                    <span class="font-semibold text-slate-900"><?= htmlspecialchars($item['title']) ?></span>
                    <?php if ($isPinnedActive): ?>
                      <span class="rounded-full bg-amber-100 px-2 py-1 text-[10px] font-semibold text-amber-700">Pinned</span>
                    <?php endif; ?>
                    <?php if ($priority > 0): ?>
                      <span class="rounded-full bg-rose-100 px-2 py-1 text-[10px] font-semibold text-rose-700"><?= htmlspecialchars($priorityLabel) ?></span>
                    <?php endif; ?>
                    <?php if (!empty($isAdmin)): ?>
                      <span class="rounded-full bg-slate-100 px-2 py-1 text-[10px] font-semibold text-slate-600"><?= htmlspecialchars($statusLabel) ?></span>
                    <?php endif; ?>
                  </div>
                  <div class="text-xs text-slate-500 line-clamp-1"><?= htmlspecialchars($item['message']) ?></div>
                </td>
                <td class="px-4 py-3 text-slate-600"><?= htmlspecialchars($scopeLabel) ?></td>
                <td class="px-4 py-3 text-slate-600">
                  <?= htmlspecialchars($start->format('d M Y, H:i')) ?><br>
                  <span class="text-xs text-slate-400">to</span> <?= htmlspecialchars($end->format('d M Y, H:i')) ?>
                </td>
                <td class="px-4 py-3">
                  <span class="inline-flex rounded-full px-2 py-1 text-xs font-semibold <?= $badge ?>"><?= $status ?></span>
                </td>
                <td class="px-4 py-3">
                  <?php if ($isAdmin): ?>
                    <a href="/announcements/<?= (int)$item['id'] ?>/edit" class="rounded-lg border border-slate-200 px-3 py-1 text-xs">Edit</a>
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
<?php
  $content = ob_get_clean();
  require __DIR__ . '/../layout.php';
?>
