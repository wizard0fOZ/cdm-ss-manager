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
    <div class="flex flex-col gap-3 lg:flex-row lg:items-end lg:justify-between">
      <form method="get" class="filter-bar grid flex-1 gap-3 md:grid-cols-5">
        <div>
          <label class="section-label">Scope</label>
          <select name="scope" class="mt-1.5 w-full rounded-xl border border-slate-200 px-3 py-2 text-sm" data-enhance="search">
            <option value="">All</option>
            <option value="GLOBAL" <?= $scope === 'GLOBAL' ? 'selected' : '' ?>>Global</option>
            <option value="CLASS" <?= $scope === 'CLASS' ? 'selected' : '' ?>>Class</option>
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
          <label class="section-label">Window</label>
          <select name="status" class="mt-1.5 w-full rounded-xl border border-slate-200 px-3 py-2 text-sm" data-enhance="search">
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
          <label class="section-label">Search</label>
          <input name="q" value="<?= htmlspecialchars($q ?? '') ?>" placeholder="Title or message" class="mt-1.5 w-full rounded-xl border border-slate-200 px-3 py-2 text-sm">
        </div>
        <div class="flex items-end gap-3 md:col-span-5">
          <label class="inline-flex items-center gap-2 text-xs text-slate-500">
            <input type="checkbox" name="show_expired" value="1" <?= $showExpired ? 'checked' : '' ?> class="rounded border-slate-300">
            Show expired
          </label>
          <button class="btn btn-primary btn-sm">Filter</button>
          <a href="/announcements" class="btn btn-secondary btn-sm">Reset</a>
        </div>
      </form>

      <?php if ($isAdmin): ?>
        <a href="/announcements/create" class="btn btn-primary btn-sm">
          <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/></svg>
          New Announcement
        </a>
      <?php endif; ?>
    </div>

    <div class="table-wrap overflow-x-auto rounded-2xl border border-slate-200">
      <table class="cdm-table w-full text-left text-sm">
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
                <?php
                  $message = 'No announcements found.';
                  if ($isAdmin) {
                    $actionLabel = 'New Announcement';
                    $actionHref = '/announcements/create';
                  }
                ?>
                <?php require __DIR__ . '/../partials/empty.php'; ?>
              </td>
            </tr>
          <?php else: ?>
            <?php foreach ($items as $item): ?>
              <?php require __DIR__ . '/_row.php'; ?>
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
