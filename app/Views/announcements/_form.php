<?php
  $csrf = $_SESSION['_csrf'] ?? '';
  $announcement = $announcement ?? [];

  $title = $announcement['title'] ?? '';
  $message = $announcement['message'] ?? '';
  $scope = $announcement['scope'] ?? 'GLOBAL';
  $classId = $announcement['class_id'] ?? '';
  $startAt = $announcement['start_at_display'] ?? ($announcement['start_at'] ?? '');
  $endAt = $announcement['end_at_display'] ?? ($announcement['end_at'] ?? '');
  $pinUntil = $announcement['pin_until_display'] ?? ($announcement['pin_until'] ?? '');
  $isPinned = !empty($announcement['is_pinned']);
  $priority = (int)($announcement['priority'] ?? 0);
  $status = $announcement['status'] ?? 'DRAFT';
?>

<?php if (!empty($errors)): ?>
  <div class="mb-5 rounded-xl border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-700">
    <ul class="list-disc pl-5">
      <?php foreach ($errors as $error): ?>
        <li><?= htmlspecialchars($error) ?></li>
      <?php endforeach; ?>
    </ul>
  </div>
<?php endif; ?>

<form method="post" action="<?= htmlspecialchars($action) ?>" class="space-y-6">
  <input type="hidden" name="_csrf" value="<?= htmlspecialchars($csrf) ?>">

  <div>
    <label class="text-sm text-slate-600">Title</label>
    <input name="title" value="<?= htmlspecialchars($title) ?>" class="mt-1 w-full rounded-xl border border-slate-200 px-3 py-2" required data-announcement-title>
  </div>

  <div>
    <label class="text-sm text-slate-600">Message</label>
    <textarea name="message" rows="4" class="mt-1 w-full rounded-xl border border-slate-200 px-3 py-2" required data-announcement-message><?= htmlspecialchars($message) ?></textarea>
  </div>

  <div class="grid gap-4 md:grid-cols-2">
    <div>
      <label class="text-sm text-slate-600">Status</label>
      <select name="status" class="mt-1 w-full rounded-xl border border-slate-200 px-3 py-2" data-enhance="search">
        <option value="DRAFT" <?= $status === 'DRAFT' ? 'selected' : '' ?>>Draft</option>
        <option value="PUBLISHED" <?= $status === 'PUBLISHED' ? 'selected' : '' ?>>Published</option>
      </select>
    </div>
    <div>
      <label class="text-sm text-slate-600">Scope</label>
      <select name="scope" class="mt-1 w-full rounded-xl border border-slate-200 px-3 py-2" data-enhance="search">
        <option value="GLOBAL" <?= $scope === 'GLOBAL' ? 'selected' : '' ?>>Global</option>
        <option value="CLASS" <?= $scope === 'CLASS' ? 'selected' : '' ?>>Class</option>
      </select>
    </div>
    <div class="md:col-span-2">
      <label class="text-sm text-slate-600">Class (if class scope)</label>
      <select name="class_id" class="mt-1 w-full rounded-xl border border-slate-200 px-3 py-2" data-enhance="search">
        <option value="">Select class</option>
        <?php foreach ($classes as $class): ?>
          <option value="<?= (int)$class['id'] ?>" <?= (string)$classId === (string)$class['id'] ? 'selected' : '' ?>><?= htmlspecialchars($class['name']) ?></option>
        <?php endforeach; ?>
      </select>
    </div>
  </div>

  <div class="grid gap-4 md:grid-cols-2">
    <div>
      <label class="text-sm text-slate-600">Priority</label>
      <select name="priority" class="mt-1 w-full rounded-xl border border-slate-200 px-3 py-2" data-enhance="search">
        <option value="0" <?= $priority === 0 ? 'selected' : '' ?>>Normal</option>
        <option value="1" <?= $priority === 1 ? 'selected' : '' ?>>High</option>
        <option value="2" <?= $priority === 2 ? 'selected' : '' ?>>Urgent</option>
      </select>
    </div>
    <div class="flex items-end">
      <label class="inline-flex items-center gap-2 text-sm text-slate-600">
        <input type="checkbox" name="is_pinned" value="1" <?= $isPinned ? 'checked' : '' ?>>
        Pin to top
      </label>
    </div>
  </div>

  <div>
    <label class="text-sm text-slate-600">Pin Until (optional)</label>
    <input type="datetime-local" name="pin_until" value="<?= htmlspecialchars($pinUntil) ?>" class="mt-1 w-full rounded-xl border border-slate-200 px-3 py-2">
    <div class="mt-1 text-xs text-slate-500">Leave empty to keep pinned indefinitely (when pinned).</div>
  </div>

  <div class="grid gap-4 md:grid-cols-2">
    <div>
      <label class="text-sm text-slate-600">Start Date & Time</label>
      <input type="datetime-local" name="start_at" value="<?= htmlspecialchars($startAt) ?>" class="mt-1 w-full rounded-xl border border-slate-200 px-3 py-2" required>
    </div>
    <div>
      <label class="text-sm text-slate-600">End Date & Time</label>
      <input type="datetime-local" name="end_at" value="<?= htmlspecialchars($endAt) ?>" class="mt-1 w-full rounded-xl border border-slate-200 px-3 py-2" required>
    </div>
  </div>

  <div class="rounded-xl border border-slate-200 bg-slate-50 px-4 py-3">
    <div class="text-xs uppercase tracking-wide text-slate-500">Preview</div>
    <div class="mt-2 text-sm font-semibold text-slate-900" data-announcement-preview-title><?= htmlspecialchars($title ?: 'Announcement title') ?></div>
    <div class="mt-1 text-sm text-slate-600" data-announcement-preview-message><?= nl2br(htmlspecialchars($message ?: 'Announcement message will appear here.')) ?></div>
  </div>

  <div class="flex items-center gap-3">
    <button class="btn btn-primary" type="submit">
      <?= htmlspecialchars($submitLabel) ?>
    </button>
    <a href="/announcements" class="btn btn-ghost btn-sm">Cancel</a>
  </div>
</form>
