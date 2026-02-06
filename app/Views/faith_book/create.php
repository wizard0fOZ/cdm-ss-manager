<?php
  $pageTitle = 'Add Faith Book Entry';
  $pageSubtitle = htmlspecialchars($student['full_name'] ?? '');

  $entry = $entry ?? [];
  $entryDate = $entry['entry_date'] ?? '';
  $entryType = $entry['entry_type'] ?? 'NOTE';
  $title = $entry['title'] ?? '';
  $content = $entry['content'] ?? '';
  $csrf = $_SESSION['_csrf'] ?? '';

  ob_start();
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

  <form method="post" action="/faith-book/<?= (int)$student['id'] ?>" class="space-y-6">
    <input type="hidden" name="_csrf" value="<?= htmlspecialchars($csrf) ?>">

    <div class="grid gap-4 md:grid-cols-2">
      <div>
      <label class="text-sm text-slate-600">Entry Date</label>
      <input type="date" name="entry_date" value="<?= htmlspecialchars($entryDate) ?>" class="mt-1 w-full rounded-xl border border-slate-200 px-3 py-2" required>
      </div>
      <div>
        <label class="text-sm text-slate-600">Type</label>
        <select name="entry_type" class="mt-1 w-full rounded-xl border border-slate-200 px-3 py-2" data-enhance="search" required>
          <?php foreach ($types as $type): ?>
            <option value="<?= $type ?>" <?= $entryType === $type ? 'selected' : '' ?>><?= $type ?></option>
          <?php endforeach; ?>
        </select>
      </div>
    </div>

    <div>
      <label class="text-sm text-slate-600">Title</label>
      <input name="title" value="<?= htmlspecialchars($title) ?>" class="mt-1 w-full rounded-xl border border-slate-200 px-3 py-2" required>
    </div>

    <div>
      <label class="text-sm text-slate-600">Notes</label>
      <textarea name="content" rows="6" class="mt-1 w-full rounded-xl border border-slate-200 px-3 py-2" required><?= htmlspecialchars($content) ?></textarea>
    </div>

    <div class="flex items-center gap-3">
      <button class="rounded-xl bg-slate-900 px-4 py-2 text-sm font-semibold text-white" type="submit">Add Entry</button>
      <a href="/faith-book/<?= (int)$student['id'] ?>" class="text-sm text-slate-600">Cancel</a>
    </div>
  </form>
<?php
  $content = ob_get_clean();
  require __DIR__ . '/../layout.php';
?>
