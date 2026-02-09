<?php
  $pageTitle = 'Students';
  $pageSubtitle = 'Search and manage student records.';

  ob_start();
?>
  <div class="flex flex-col gap-4">
    <div class="flex flex-col gap-3 lg:flex-row lg:items-end lg:justify-between">
      <form method="get"
            class="filter-bar grid flex-1 gap-3 md:grid-cols-4"
            hx-get="/students/partial"
            hx-trigger="change, submit, keyup delay:300ms from:input[name='q']"
            hx-target="#students-table"
            hx-swap="outerHTML"
            hx-push-url="true"
            hx-indicator="#students-loading">
        <div>
          <label class="section-label">Search</label>
          <input name="q" value="<?= htmlspecialchars($search ?? '') ?>" placeholder="Name or ID" class="mt-1.5 w-full rounded-xl border border-slate-200 px-3 py-2 text-sm">
        </div>
        <div>
          <label class="section-label">Status</label>
          <select name="status" class="mt-1.5 w-full rounded-xl border border-slate-200 px-3 py-2 text-sm" data-enhance="search">
            <option value="">All</option>
            <?php foreach (['ACTIVE','INACTIVE','GRADUATED','TRANSFERRED'] as $opt): ?>
              <option value="<?= $opt ?>" <?= ($status ?? '') === $opt ? 'selected' : '' ?>><?= $opt ?></option>
            <?php endforeach; ?>
          </select>
        </div>
        <div>
          <label class="section-label">Class</label>
          <select name="class_id" class="mt-1.5 w-full rounded-xl border border-slate-200 px-3 py-2 text-sm" data-enhance="search">
            <option value="">All</option>
            <?php foreach ($classes as $class): ?>
              <option value="<?= (int)$class['id'] ?>" <?= (string)($classId ?? '') === (string)$class['id'] ? 'selected' : '' ?>>
                <?= htmlspecialchars($class['name']) ?>
              </option>
            <?php endforeach; ?>
          </select>
        </div>
        <div class="flex items-end gap-2">
          <button class="btn btn-primary btn-sm">Filter</button>
          <a href="/students" class="btn btn-secondary btn-sm">Reset</a>
        </div>
      </form>
      <div class="flex items-center gap-3">
        <div id="students-loading" class="htmx-indicator loading-indicator text-xs text-slate-500" style="display:none;">Loading...</div>
        <a href="/students/create" class="btn btn-primary btn-sm">
          <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/></svg>
          Add Student
        </a>
      </div>
    </div>

    <form id="bulk-students-form" method="post" action="/students/bulk">
      <div class="filter-bar flex flex-wrap items-center gap-3">
        <input type="hidden" name="_csrf" value="<?= htmlspecialchars($_SESSION['_csrf'] ?? '') ?>">
        <label class="section-label">Bulk Action</label>
        <select name="bulk_action" class="rounded-lg border border-slate-200 px-3 py-2 text-sm" required>
          <option value="">Select</option>
          <option value="set_status">Set Status</option>
          <option value="assign_class">Assign Class</option>
        </select>
        <select name="status" class="rounded-lg border border-slate-200 px-3 py-2 text-sm" data-enhance="search">
          <option value="">Status</option>
          <?php foreach (['ACTIVE','INACTIVE','GRADUATED','TRANSFERRED'] as $opt): ?>
            <option value="<?= $opt ?>"><?= $opt ?></option>
          <?php endforeach; ?>
        </select>
        <select name="class_id" class="rounded-lg border border-slate-200 px-3 py-2 text-sm" data-enhance="search">
          <option value="">Class</option>
          <?php foreach ($classes as $class): ?>
            <option value="<?= (int)$class['id'] ?>"><?= htmlspecialchars($class['name']) ?></option>
          <?php endforeach; ?>
        </select>
        <button class="btn btn-primary btn-sm"
                data-confirm
                data-confirm-title="Apply Bulk Action"
                data-confirm-message="This will apply the selected action to the chosen students. Continue?"
                data-confirm-text="Apply"
                data-confirm-form="bulk-students-form">Apply</button>
        <span class="text-xs text-slate-400">Select students below.</span>
      </div>

      <?php require __DIR__ . '/_table.php'; ?>
    </form>
  </div>
<?php
  $content = ob_get_clean();
  require __DIR__ . '/../layout.php';
?>
