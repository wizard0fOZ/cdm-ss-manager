<?php
  $csrf = $_SESSION['_csrf'] ?? '';
  $lesson = $lesson ?? [];

  $classId = $lesson['class_id'] ?? '';
  $sessionDate = $lesson['session_date'] ?? '';
  $title = $lesson['title'] ?? '';
  $description = $lesson['description'] ?? '';
  $content = $lesson['content'] ?? '';
  $url = $lesson['url'] ?? '';
  $status = $lesson['status'] ?? 'DRAFT';
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

  <div class="grid gap-4 md:grid-cols-2">
    <div>
      <label class="text-sm text-slate-600">Class</label>
      <select name="class_id" class="mt-1 w-full rounded-xl border border-slate-200 px-3 py-2" data-enhance="search" required>
        <option value="">Select class</option>
        <?php foreach ($classes as $class): ?>
          <option value="<?= (int)$class['id'] ?>" <?= (string)$classId === (string)$class['id'] ? 'selected' : '' ?>><?= htmlspecialchars($class['name']) ?></option>
        <?php endforeach; ?>
      </select>
    </div>
    <div>
      <label class="text-sm text-slate-600">Lesson Date</label>
      <input type="date" name="session_date" value="<?= htmlspecialchars($sessionDate) ?>" class="mt-1 w-full rounded-xl border border-slate-200 px-3 py-2" required>
    </div>
  </div>

  <div>
    <label class="text-sm text-slate-600">Title</label>
    <input name="title" value="<?= htmlspecialchars($title) ?>" class="mt-1 w-full rounded-xl border border-slate-200 px-3 py-2" required>
  </div>

  <div>
    <label class="text-sm text-slate-600">Summary / Description</label>
    <textarea name="description" rows="3" class="mt-1 w-full rounded-xl border border-slate-200 px-3 py-2"><?= htmlspecialchars($description) ?></textarea>
  </div>

  <div>
    <label class="text-sm text-slate-600">Lesson Content</label>
    <textarea name="content" rows="6" class="mt-1 w-full rounded-xl border border-slate-200 px-3 py-2" required><?= htmlspecialchars($content) ?></textarea>
  </div>

  <div>
    <label class="text-sm text-slate-600">Resource URL (optional)</label>
    <input name="url" value="<?= htmlspecialchars($url) ?>" class="mt-1 w-full rounded-xl border border-slate-200 px-3 py-2" placeholder="https://">
  </div>

  <div>
    <label class="text-sm text-slate-600">Status</label>
    <select name="status" class="mt-1 w-full rounded-xl border border-slate-200 px-3 py-2" data-enhance="search" required>
      <?php foreach ($statuses as $opt): ?>
        <option value="<?= $opt ?>" <?= $status === $opt ? 'selected' : '' ?>><?= $opt ?></option>
      <?php endforeach; ?>
    </select>
  </div>

  <div class="flex items-center gap-3">
    <button class="rounded-xl bg-slate-900 px-4 py-2 text-sm font-semibold text-white" type="submit">
      <?= htmlspecialchars($submitLabel) ?>
    </button>
    <a href="/lessons" class="text-sm text-slate-600">Cancel</a>
  </div>
</form>
